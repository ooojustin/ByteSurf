import http.cookiejar
import requests
import pathlib
from clint.textui import progress

def generate_cookie(cookie_raw):
    """
    Creates a http.cookiejar.Cookie object, given raw cookie information as dict.
    This dict must contain the following keys: name, value, domain, path, secure

    Parameters:
        cookie_raw (dict): The cookie information dictionary.

    Returns:
        http.cookiejar.Cookie: The generated cookie object.
    """

    # cookie object constructor:
    # https://github.com/python/cpython/blob/c76add7afd68387aa2481d672e1c0d7e7b4c9afc/Lib/http/cookiejar.py#L747

    # expiry is optional, so default it to false if not set
    if not 'expiry' in cookie_raw:
        cookie_raw['expiry'] = False

    # initialize Cookie object
    cookie = http.cookiejar.Cookie(
        0,                      # version
        cookie_raw['name'],     # name
        cookie_raw['value'],    # value
        None,                   # port
        False,                  # port_specified
        cookie_raw['domain'],   # domain
        True,                   # domain_specified
        "",                     # domain_initial_dot
        cookie_raw['path'],     # path
        True,                   # path_specified,
        cookie_raw['secure'],   # secure
        cookie_raw['expiry'],   # expires
        False,                  # discord
        "",                     # comment
        "",                     # comment_url
        None,                   # rest
        )

    return cookie

def session_from_driver(browser):
    """
    Creates a 'requests.Session' object to make requests from.
    Automatically copies cookies from selenium driver into new session.

    Parameters:
        browser (selenium.webdriver): An instance of a selenium webdriver.

    Returns:
        requests.Session: A session containing cookies from the provided selenium.webdriver object.
    """

    cookies = browser.get_cookies()
    session = requests.session()

    for cookie_raw in cookies:
        cookie = generate_cookie(cookie_raw)
        session.cookies.set_cookie(cookie)

    return session

def get_language_label(srclang):
    """
    Converts a srclang value (639-1) to a language name.
    https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes

    Parameters:
        srclang (string): An ISO 639-1 language value.

    Returns:
        string: Name of the language.
    """
    labels = {
        "en": "English",
        "fr": "French",
        # ...
    }
    return labels.get(srclang, "?")
