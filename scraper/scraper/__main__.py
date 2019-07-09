import flixify
import json
import database

database.connect()
scraper = flixify.login("justin@garofolo.net", "D3MU&DvWm9%xf*z")

for genre in flixify.GENRES:

    page = 1

    while True:

        data = scraper.download_data("movies", page, genre)

        # if we're out of videos
        if not data:
            print("FINISHED GENRE: " + genre)
            break

        # if the number of movies is 0 (???)
        # i dont think this will ever happen
        movies = data['items']
        if len(movies) == 0:
            break

        for movie in movies:

            # skip movie if we already have it
            slug = movie["url"].replace("/movies/", "")
            if database.get_movie(slug):
                continue

            # upload movie to database and print something
            movie = scraper.get_movie_data(movie)
            database.upload_movie(movie)
            print("MOVIE UPLOADED: " + slug)

        page += 1

print("COMPLETED")
