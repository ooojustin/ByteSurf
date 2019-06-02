import flixify

flixify = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")
page = 1

while True:

    data = flixify.download_data("movies", page)

    movies = data['items']
    if len(movies) == 0:
        break

    for movie in movies:
        print(movie['title'] + " - " + movie['url'])

    page += 1
