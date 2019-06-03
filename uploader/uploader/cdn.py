import ftplib, os, json, flixify, requests, utils

cdn_url = "https://cdn.bytesurf.io/"

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

def reupload_file(url, filename):
    """
    Downloads a file from a url and uploads it to the current working directory of the ftp connection.

    Parameters:
        url (string): The url of the file online.
        filename (string): The name of file to reupload to.
    """
    print("reuploading file: " + filename)
    utils.download_file(url, filename)
    with open(filename,'rb') as file:
        ftp.storbinary("STOR " + filename, file)
    os.remove(filename)

