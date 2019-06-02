import flixify

flixify = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")
page = 1
count = 1

while True:

    data = flixify.download_data("movies", page)
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
