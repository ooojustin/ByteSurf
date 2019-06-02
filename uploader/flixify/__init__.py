# import selenium, for original login
from selenium import webdriver

# import general modules
import urllib, time, json, requests

# import my utils.py file & functions
import utils
import pyperclip

class flixify:

    # base url of the flixify site
    SITE_URL = "https://calmx.site/"

    def __init__(self, email, password):

        # initializes webdriver
        # note: firefox uses geckodriver, so it must be in PATH
        # https://github.com/mozilla/geckodriver
        browser = webdriver.Firefox()

        # load login page
        print("Loading Flixify...")
        browser.get(self.SITE_URL + "login")

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

        cookies = browser.get_cookies()
        browser.quit()

        self.session = requests.session()
        for cookie_raw in cookies:
            cookie = utils.generate_cookie(cookie_raw)
            self.session.cookies.set_cookie(cookie)

    def build_get_url(self, type, page):
        """
        Builds a flixify API url from a type & page.

        Parameters:
            type (string): Type of content. ('movies' or 'shows')
            page (int): Page to get items from.

        Returns:
            string: The URL of the API to send a request to.
        """

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

        return "{}/{}?{}".format(self.SITE_URL, type, urllib.parse.urlencode(params))

    def download_data(self, type, page):
        """
        Downloads json data from flixify API URL, deserializes it into dict object.

        Parameters:
            type (string): Type of content. ('movies' or 'shows')
            page (int): Page to get items from.

        Returns:
            dict: A dictionary containing values from the response. Example data from download_data('movies', 1): https://pastebin.com/wq7PvT7F
        """

        headers = {
            "Accept": "application/json",
            "Referer": self.referer_url,
            "TE": "Trailers",
            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:67.0) Gecko/20100101 Firefox/67.0"
        }

        url = self.build_get_url(type, page)
        response = self.session.get(url, headers = headers)
        pyperclip.copy(response.text)
        if response.status_code == 200:
            return json.loads(response.text)
        else: return false

def login(email, password):
    """
    Automatically sumit flixify login and create instance to download data.

    Parameters:
        email (string): Flixify account email.
        password (string): Flixify account password.
    """
    return flixify(email, password)
