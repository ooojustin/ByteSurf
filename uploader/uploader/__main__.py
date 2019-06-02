import flixify

scraper = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")
page = 1
count = 1

while True:

    genre = flixify.GENRES[0]
    data = scraper.download_data("movies", page, genre)

    # if we're out of videos
    if not data:
        print("failed: download_data('{}', {})".format("movies", page))
        break

    movies = data['items']
    if len(movies) == 0:
        break

    for movie in movies:
        print(str(count) + ": " + movie['title'])
        count += 1

    page += 1
