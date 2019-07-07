import flixify
import json

scraper = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")

data = scraper.download_data("movies", 1, flixify.GENRES[0])
movies = data['items']
movie = movies[0]
movie_data = scraper.get_movie_data(movie)
print(json.dumps(movie_data))

# for genre in flixify.GENRES:
#
#     page = 1
#
#     while True:
#
#         data = scraper.download_data("movies", page, genre)
#
#         # if we're out of videos
#         if not data:
#             print("FINISHED GENRE: " + genre)
#             break
#
#         movies = data['items']
#         if len(movies) == 0:
#             break
#
#         for movie in movies:
#
#             # fix movie url (remove the '/movies/' text from the start)
#             url = movie["url"].replace("/movies/", "")
#
#             # print movie title
#             print("Movie found: " + movie["title"] + " (genre: {})".format(genre))
#
#         page += 1
#
# print("COMPLETED")
