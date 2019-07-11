import flixify, time

# flixify account credentials
EMAIL = "justin@garofolo.net"
PASSWORD = "D3MU&DvWm9%xf*z"

scraper = flixify.scraper(EMAIL, PASSWORD)
scraper.start()

# keep this main thread alive so i can kill w/ ctrl + c
while True:
    time.sleep(1)
