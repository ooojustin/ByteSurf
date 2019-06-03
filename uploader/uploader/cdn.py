import ftplib, os, json, flixify, requests, utils

cdn_url = "https://cdn.bytesurf.io/"

# initialize the ftp connection
if os.path.isfile('database.cfg'):
    print("Initiailizing ftp connection...", end = " ")
    ftp_cfg = json.loads(open('ftp.cfg', 'r').read())
    ftp = ftplib.FTP(
        ftp_cfg['host'],
        ftp_cfg['username'],
        ftp_cfg['password']
    )
    print("done")

def reupload_file(url, filename):
    """
    Downloads a file from a url and uploads it to the current working directory of the ftp connection.

    Parameters:
        url (string): The url of the file online.
        filename (string): The name of file to reupload to.
    """
    print("reuploading file: " + filename)
    utils.download_file(url, filename)
    with open(filename,'rb') as file:
        ftp.storbinary("STOR " + filename, file)
    os.remove(filename)

def upload_movie(movie):
    """
    Uploads a movie to cdn server, inserts into sql database.

    Parameters:
        movie (dict): Movie data, retrieved from the flixify.get_movie_data function.
    """

    # create dir on cdn server & enter it
    path = movie["url"][1:]
    ftp.mkd(path)
    ftp.cwd(path)
    path_url = cdn_url + path

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
        reupload_file(image_url, image_file)
        values.append(path_url + "/" + image_file)

    upload_image("poster", "thumbnail")         # thumbnail
    upload_image("preview_large", "preview")    # preview

    # qualities
    qualities = list()
    for resolution, url in movie["media"].items():

        # reupload the file to our server
        file = str(resolution) + ".mp4"
        reupload_file(url, file)

        # create the quality dict (store resolution & link to file)
        quality = dict()
        quality["resolution"] = resolution
        quality["link"] = path_url + "/" + file
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
        reupload_file(url, name + ".vtt")

        # determine subtitle info to store in database, add to subtitles List
        subtitle = dict()
        subtitle["language"] = name
        subtitle["label"] = utils.get_language_label(name)
        subtitle["url"] = cdn_url + path + "/{}.vtt".format(name)
        subtitles.append(subtitle)

    videos.append(json.dumps(subtitles))

    # certification
    values.append(movie["certification"])

    print(values)
