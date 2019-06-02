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
