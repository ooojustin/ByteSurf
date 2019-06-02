import flixify

flixify = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")
data = flixify.download_data("movies", 1)
movies = data['items']
for movie in movies:
    print(movie['title'] + " - " + movie['url'])
