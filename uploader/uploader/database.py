import mysql.connector, json

# initialize the database connection
print("Initiailizing database connection...", end = " ")
db_cfg = json.loads(open('database.cfg', 'r').read())
db = mysql.connector.connect(
    host = db_cfg['host'],
    user = db_cfg['username'],
    password = db_cfg['password']
)
db.autocommit = True # automatically commit changes to db (ex: insert queries)
cursor = db.cursor() # object used to execute commands
cursor.execute("USE " + db_cfg['database']) # start using the specified database on the MySQL server
print("done")

def get_movie(url):
    """
    Gets a movie from the database, based on it's url.

    Parameters:
        url (string): Last part of the url, used to uniquely identify the movie. (Example: 'the-grinch-2018')

    Returns:
        tuple: The movie row from the database.
    """
    cursor.execute("SELECT * FROM movies WHERE url = '{}'".format(url))
    return cursor.fetchone()
