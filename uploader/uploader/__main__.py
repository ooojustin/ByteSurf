import flixify, cdn, database

scraper = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")

data = scraper.download_data("movies", 1)
movies = data["items"]

for m in movies:
    if m["url"].endswith("captain-marvel-2019"):
        movie = m
        break

movie = scraper.get_movie_data(movie)
cdn.upload_movie(movie)
print("completed")

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
#             # skip this movie if it already exists on the server
#             if database.get_movie(movie["url"]) is not None: continue
#
#             # print movie title
#             print("Movie found: " + movie["title"] + " (genre: {})".format(genre))
#
#         page += 1
#
# print("COMPLETED")
