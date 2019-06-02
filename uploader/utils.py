import http.cookiejar

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
        0,                   # version
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
        False)

    return cookie
