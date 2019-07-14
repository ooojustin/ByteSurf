import flixify, time

# flixify account credentials
EMAIL = "justin@garofolo.net"
PASSWORD = "D3MU&DvWm9%xf*z"

# create the actual account used as a scraper (instance of 'flixify' class)
account = flixify.login(EMAIL, PASSWORD)

# # start thread to get new movies from flixify
# scraper = flixify.scraper(account)
# scraper.start()

# start thread to update mp4 links from Flixify
updater = flixify.updater(account)
updater.start()

# keep this main thread alive so i can kill w/ ctrl + c
while True:
    time.sleep(1)
