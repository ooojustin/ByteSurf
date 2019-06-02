import ftplib, os, json

# initialize the ftp connection
if os.path.isfile('database.cfg'):
    print("Initiailizing ftp connection...", end = " ")
    ftp_cfg = json.loads(open('ftp.cfg', 'r').read())
    ftp = ftplib.FTP(
        ftp_cfg['host'],
        ftp_cfg['username'],
        ftp_cfg['password']
    )
    print("done")
