import os, json, flixify, requests, utils

access_key = "ce726c9e-edcc-4adb-839edc6148bb-7807-4e03"

def reupload_file(url, putter):
    """
    Downloads a file from a url and uploads it to the using bunnycdn storage API.

    Parameters:
        url (string): The url of the file online.
        filename (string): The name of file to reupload to.
    """
    filename = utils.get_file_name(putter)
    print("reuploading file: " + filename)
    utils.download_file(url, filename)
    with open(filename,'rb') as file:
        requests.put(putter, data = file, headers = { "AccessKey": access_key })
    os.remove(filename)

def upload_movie(movie):
    """
    Uploads a movie to cdn server, inserts into sql database.

    Parameters:
        movie (dict): Movie data, retrieved from the flixify.get_movie_data function.
    """

    path = movie["url"][1:] + "/"
    path_url = "https://cdn.bytesurf.io/" + path
    put_url = "https://storage.bunnycdn.com/jexflix/" + path

    # list of values to insert into new row in 'movies' table
    # title, url, description, duration, thumbnail, preview, qualities, genres, imdb_id, rating, year, subtitles, certification
    values = list()
    values.append(movie["title"])
    values.append(movie["url_short"])
    values.append(movie["description"])
    values.append(movie["duration"])

    # upload images
    def upload_image(name, file):
        image_url = flixify.ASSETS_URL[:-1] + movie["images"][name]
        image_file = file + utils.get_extension(image_url)
        reupload_file(image_url, put_url + image_file)
        values.append(path_url + image_file)

    upload_image("poster", "thumbnail")         # thumbnail
    upload_image("preview_large", "preview")    # preview

    # qualities
    qualities = list()
    for resolution, url in movie["media"].items():

        # reupload the file to our server
        file = str(resolution) + ".mp4"
        reupload_file(url, put_url + file)

        # create the quality dict (store resolution & link to file)
        quality = dict()
        quality["resolution"] = resolution
        quality["link"] = path_url + file
        qualities.append(quality)

    values.append(json.dumps(qualities))

    # genres, imdb_id, rating, year
    values.append(json.dumps(movie["genres"]))
    values.append(movie["imdb_id"])
    values.append(movie["rating"])
    values.append(movie["year"])

    # subtitles
    subtitles = list()
    for name, sub in  movie["subtitles"].items():

        # note: subtitle name is stored as 3 char (639-2/T), remove last char to convert to 639-1
        # https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
        name = name[:-1]
        sub = sub[0]

        # determine subtitle url on server
        url = flixify.ASSETS_URL[:-1] + sub["url"]
        reupload_file(url, put_url + name + ".vtt")

        # determine subtitle info to store in database, add to subtitles List
        subtitle = dict()
        subtitle["language"] = name
        subtitle["label"] = utils.get_language_label(name)
        subtitle["url"] = path_url + name + ".vtt"
        subtitles.append(subtitle)

    videos.append(json.dumps(subtitles))

    # certification
    values.append(movie["certification"])

    print(values)
