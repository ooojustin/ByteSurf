import mysql.connector, json, os, utils
from datetime import datetime

# documentation for mysql.connector:
# https://dev.mysql.com/doc/connector-python/en/connector-python-reference.html
# https://dev.mysql.com/doc/connector-python/en/connector-python-api-mysqlcursor-execute.html

db = None
cursor = None

def connect():

    global db, cursor

    # make sure we're not already connected
    if db and db.is_connected(): return

    # get config data
    if not os.path.isfile("database.cfg"): return
    db_cfg = json.loads(open('database.cfg', 'r').read())

    # connect to db
    print("Initiailizing database connection...", end = " ")
    db = mysql.connector.connect(
        host = db_cfg['host'],
        user = db_cfg['username'],
        password = db_cfg['password']
    )

    # connection initialization
    db.autocommit = True # automatically commit changes to db (ex: insert queries)
    cursor = db.cursor() # object used to execute commands
    cursor.execute("USE " + db_cfg['database']) # start using the specified database on the MySQL server
    print("done")

def get_movie(slug):
    """
    Gets a movie from our database given the slug.

    Parameters:
        slug (string): The last part of the URL, used to uniquely identify a movie.
    Returns:
        tuple: The movie row, each columns data is am item in the tuple. Returns 'None' if no movie is found.
    """
    connect()
    query = "SELECT * FROM movies WHERE slug = %s"
    params = tuple([slug])
    cursor.execute(query, params)
    return cursor.fetchone()

def upload_movie(movie):
    """
    Uploads a movie to our database.

    Parameters:
        movie (dict): A dictionary of data returned by flixify.get_movie_data.
    """
    connect()
    columns = [
        "slug", "title", "description",
        "duration", "year", "certification",
        "genres", "rating", "imdb",
        "poster", "preview", "media", "updated"
        ]

    # build insert query
    query = utils.build_insert_query("movies", columns)

    # build list of values
    data = list()
    data.extend([movie["slug"], movie["title"], movie["description"]])
    data.extend([movie["duration"], movie["year"], movie["certification"]])
    data.extend([json.dumps(movie["genres"]), movie["rating"], movie["imdb_id"]])
    data.extend([movie["images"]["poster"], movie["images"]["preview"]])
    data.extend([json.dumps(movie["media"]), datetime.utcnow()])

    # execute query :)
    cursor.execute(query, data)

def get_movies_pending_update():
    """
    Gets a list of movies that need to have their mp4 links updated.

    Returns:
        movies (list): A list of tuples, where each item in a given tuple is a row.
    """
    connect()
    query = "SELECT * FROM movies WHERE update_required = 1"
    cursor.execute(query)
    return cursor.fetchall()

def update_movie(slug, movie):
    """
    Updates a movies information in the database.
    Only some variables may need to be updated.

    Parameters:
        slug (string): The last part of the URL, used to uniquely identify a movie.
        movie (dict): A dictionary of data returned by flixify.get_movie_data.
    """
    connect()
    query = "UPDATE movies SET media = %s, updated = %s, update_required = 0 WHERE slug = %s"
    data = [json.dumps(movie["media"]), datetime.utcnow(), slug]
    cursor.execute(query, data)
