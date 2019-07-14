import flixify, time

# flixify account credentials
EMAIL = "justin@garofolo.net"
PASSWORD = "D3MU&DvWm9%xf*z"

# start thread to get new movies from flixify
scraper = flixify.scraper(EMAIL, PASSWORD)
scraper.start()

# start thread to update mp4 links from Flixify
updater = flixify.updater()
updater.start()

# keep this main thread alive so i can kill w/ ctrl + c
while True:
    time.sleep(1)
