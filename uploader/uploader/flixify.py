# import selenium, for original login
from selenium import webdriver

# import general modules
import urllib, time, json, requests

# import uploader.utils module
import utils

# flixify urls
SITE_URL = "https://calmx.site/"
ASSETS_URL = "https://a.calmx.site/"

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
        options.set_headless(True)
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

    def get_movie_data(self, movie):
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
        url = SITE_URL + movie['url'][1:]
        url += "?" + urllib.parse.urlencode(params)

        # send & parse request
        response = self.session.get(url, headers = self.headers)
        if response.status_code == 200:
            data = json.loads(response.text)
            return data['item']
        else:
            return False

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
    """
    Automatically submit flixify login and create instance to download data.

    Parameters:
        email (string): Flixify account email.
        password (string): Flixify account password.

    Returns:
        flixify: Flixify class instance, with session.
    """
    return flixify(email, password)
