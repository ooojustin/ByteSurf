# import selenium, for original login
from selenium import webdriver

# import general modules
import urllib, time, json, requests, threading
import database, utils


# flixify urls
SITE_URL = "https://calmx.site/"
ASSETS_URL = "https://a.calmx.site/"

# whether or not we should hide the browser window when logging in
HIDE_BROWSER = True

# list of genres on flixify
GENRES = (
    "animation",            # 0
    "fantasy",              # 1
    "science-fiction",      # 2
    "music",                # 3
    "documentary",          # 4
    "western",              # 5
    "action",               # 6
    "comedy",               # 7
    "drama",                # 8
    "history",              # 9
    "mystery",              # 10
    "thriller",             # 11
    "adventure",            # 12
    "crime",                # 13
    "family",               # 14
    "horror",               # 15
    "romance",              # 16
    "war"                   # 17
    )

class flixify:

    def __init__(self, email, password):

        # initializes webdriver
        # note: firefox uses geckodriver, so it must be in PATH
        # https://github.com/mozilla/geckodriver
        print("Initializing FireFox driver...", end = " ")
        options = webdriver.firefox.options.Options()
        options.set_headless(HIDE_BROWSER)
        browser = webdriver.Firefox(firefox_options = options)
        print("done")

        # store user-agent for future use
        self.user_agent = browser.execute_script("return navigator.userAgent")

        # load login page
        print("Loading Flixify...", end = " ")
        browser.get(SITE_URL + "login")
        print("done")

        # login to the website (set username and password, click submit)
        print("Logging in...")
        browser.find_elements_by_name("email")[0].send_keys(email)
        browser.find_elements_by_name("password")[0].send_keys(password)
        browser.find_element_by_xpath("//input[@value='LOGIN']").click()

        # determine '_t' and '_u' variables
        # these are loaded via javascript, so we may have to wait
        while True:

            # parse url and determine query parameter
            url_info = urllib.parse.urlparse(browser.current_url)
            query_info = urllib.parse.parse_qs(url_info.query)

            # if we're missing '_t' or '_u', wait a second and try again
            if not '_t' in query_info or not '_u' in query_info:
                time.sleep(1)
                continue

            # store values and break out of loop
            self.var_t = query_info['_t'][0]
            self.var_u = query_info['_u'][0]
            break

        self.referer_url = browser.current_url
        print("Logged in successfully!")
        print("_t variable: " + self.var_t)
        print("_u variable: " + self.var_u)

        self.session = utils.session_from_driver(browser)
        browser.quit()

        self.headers = {
            "User-Agent": self.user_agent,
            "Referer": self.referer_url,
            "Accept": "application/json",
            "TE": "Trailers"
        }

    def build_list_url(self, type, page, genre = None):
        """
        Builds a flixify api url from a type & page.
        This will retrieve a list of 'items', aka movies/shows.

        Parameters:
            type (string): Type of content. ('movies' or 'shows')
            page (int): Page to get items from.
            genre (string): Genre to get videos from.

        Returns:
            string: The url of the api to send a request to.
        """

        # build standard parameters
        params = {
            "_t": self.var_t,
            "_u": self.var_u,
            "add_mroot": 1,
            "description": 1,
            "o": "t",
            "p": page,
            "postersize": "poster",
            "previewsizes": '{"preview_list":"big3-index","preview_grid":"video-block"}',
            "slug": 1,
            "type": type
        }

        # define genre, if specified
        if genre: params['g'] = genre

        # return formatted url
        return "{}/{}?{}".format(SITE_URL, type, urllib.parse.urlencode(params))

    @staticmethod
    def process_movie_data(movie):
        """
        Processing of movie data before returning it in get_movie_data() function.

        Parameters:
            movie (dict): Full movie data from flixify. See flixify.get_movie_data().
        Returns:
            dict: Modified movie data.
        """
        try:

            # store the slug (last part of the url)
            movie["slug"] = movie["url"].replace("/movies/", "")

            # fix images (turn paths into full urls)
            BASE_URL = "https://a.flixify.com"
            images = movie["images"]
            previews = images["previews"]
            images["preview"] = BASE_URL + images["preview"]
            images["poster"] = BASE_URL + images["poster"]
            images["preview_large"] = BASE_URL + images["preview_large"]
            for img, path in enumerate(previews):
                previews[img] = BASE_URL + path

            if movie["certification"] is None: movie["certification"] = "N/A"
            if movie["year"] is None: movie["year"] = 0
            if movie["rating"] is None: movie["rating"] = 0

            if not "imdb_id" in movie: movie["imdb_id"] = ""


            return movie

        except: return False

    def get_movie_data(self, slug):
        """
        Gets data from flixify about a specific movie.

        Parameters:
            movie (dict): Item from items list returned by a download_data.

        Returns:
            dict: Full movie data from flixify. Example: https://pastebin.com/tW8idgyA
        """

        params = {
            "_t": self.var_t,
            "_u": self.var_u,
            "add_mroot": 1,
            "add_sequels": 1,
            "cast": 0,
            "crew": 0,
            "description": 1,
            "episodes_list": 1,
            "postersize": "poster",
            "previews": 1,
            "previewsizes": '{"preview_list":"big3-index","preview_grid":"video-block"}',
            "season_list": 1,
            "slug": 1,
            "sub": 1
        }

        # generate url
        url = SITE_URL + "movies/" + slug
        url += "?" + urllib.parse.urlencode(params)

        # send & parse request
        response = self.session.get(url, headers = self.headers)
        if response.status_code == 200:

            # parse movie data from response
            data = json.loads(response.text)
            movie = data["item"]

            # make any necessary modifications and return
            movie = flixify.process_movie_data(movie)
            return movie

        else: return False

    def download_data(self, type, page, genre = None):
        """
        Downloads json data from flixify api url, deserializes it into dict object.

        Parameters:
            type (string): Type of content. ('movies' or 'shows')
            page (int): Page to get items from.
            genre (string): Genre to get videos from.

        Returns:
            dict: A dictionary containing values from the response. Example data from download_data('movies', 1): https://pastebin.com/wq7PvT7F
        """

        url = self.build_list_url(type, page, genre)
        response = self.session.get(url, headers = self.headers)
        if response.status_code == 200:
            return json.loads(response.text)
        else: return False

def login(email, password):
    """Shortcut to flixify.flixify constructor from external modules."""
    return flixify(email, password)

class scraper(threading.Thread):

    def __init__(self, scraper):
        threading.Thread.__init__(self)
        self.scraper = scraper
        # note: this may need to automatically re-login eventually?

    def handle_movie(self, movie):
        """
        Uploads a movie to the database, if we don't already have it.

        Parameters:
            movie (dict): Movie data from list returned by flixify.download_data().
        """

        # skip movie if we already have it
        slug = movie["url"].replace("/movies/", "")
        print(slug)
        if database.get_movie(slug):
            return False

        # upload movie to database
        print("movie: " + slug)
        movie = self.scraper.get_movie_data(slug)
        if not movie:
            print("failed")
        else:
            database.upload_movie(movie)

    def run_once(self):
        """Loops through every single movie on flixify and stores it if we don't already have it."""

        for genre in GENRES:

            # start at page 1, keep incrementing until we break
            page = 1
            while True:

                print("genre: {}, page: {}\n".format(genre, page))
                data = self.scraper.download_data("movies", page, genre)

                # if we're out of videos
                if not data: break

                # make sure we have some movies
                movies = data['items']
                if len(movies) == 0: break

                for movie in movies:
                    self.handle_movie(movie)
                    print("------------------------------------\n")

                page += 1

    def run(self):
        """Runs run_once every hour to upload all flixify movie data."""

        # run scraper every hour
        while True:
            self.run_once()
            time.sleep(60 * 60)

class updater(threading.Thread):

    def __init__(self, scraper):
        threading.Thread.__init__(self)
        self.scraper = scraper

    def run_once(self):

        # loop through movies that need to be updated
        for movie in database.get_movies_pending_update():

            # get movie data from slug
            slug = movie[1]
            movie = self.scraper.get_movie_data(slug)

            # update the row in the database
            database.update_movie(slug, movie)

    def run(self):
        "Runs run_once every minute to update all movie links where update_required = 1 in database."

        # run update every minute
        while True:
            self.run_once()
            time.sleep(60)
