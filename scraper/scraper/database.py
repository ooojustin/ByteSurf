import mysql.connector, json, os

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
