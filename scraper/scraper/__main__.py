import flixify
import json
import database

database.connect()
scraper = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")

for genre in flixify.GENRES:

    page = 1

    while True:

        print("genre: {}, page: {}\n".format(genre, page))
        data = scraper.download_data("movies", page, genre)

        # if we're out of videos
        if not data: break

        # make sure we have some movies
        movies = data['items']
        if len(movies) == 0: break

        for movie in movies:

            # skip movie if we already have it
            slug = movie["url"].replace("/movies/", "")
            if database.get_movie(slug):
                continue

            # upload movie to database
            print("movie: " + slug)
            movie = scraper.get_movie_data(movie)
            if not movie:
                print("failed")
            else:
                print(movie)
                database.upload_movie(movie)

            print("------------------------------------\n")

        page += 1

print("completed")
