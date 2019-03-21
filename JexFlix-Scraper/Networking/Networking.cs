using CloudFlareUtilities;
using JexFlix_Scraper.Anime.Misc;
using JexFlix_Scraper.Flixify;
using SafeRequest;
using System;
using System.Collections.Specialized;
using System.IO;
using System.Net;
using System.Net.Http;
using System.Threading;

namespace JexFlix_Scraper {

    public static class Networking {

        /// <summary>
        /// Public link to access files uploaded to the JexFlix FTP/CDN server.
        /// </summary>
        public const string CDN_URL = "https://cdn.jexflix.com";

        /// <summary>
        /// Credentials to CDN hosting FTP server.
        /// Used to upload movies and whatnot owo.
        /// </summary>
        private static NetworkCredential FTP_CREDENTIALS = new NetworkCredential("jexflix", "ce726c9e-edcc-4adb-839edc6148bb-7807-4e03");

        /// <summary>
        /// Encryption key used to protect data transferred between scraper and scraper.jexflix.com server.
        /// </summary>
        private const string ENCRYPTION_KEY = "jexflix";

        /// <summary>
        /// SafeRequest class, operates as a constant - should not be modified.
        /// </summary>
        public static SafeRequest.SafeRequest SAFE_REQUEST = new SafeRequest.SafeRequest(ENCRYPTION_KEY);

        /// <summary>
        /// Common User-Agent accepted by all websites.
        /// Must be added to some web requests to prevent them from being blocked.
        /// </summary>
        public const string USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/72.0.3626.121 Safari/537.36";

        /// <summary>
        /// Bypass CloudFlare on a specified domain.
        /// https://github.com/elcattivo/CloudFlareUtilities
        /// </summary>
        public static void BypassCloudFlare(string domain, out CookieContainer cookies) {
            ClearanceHandler handler = new ClearanceHandler();
            HttpClient client = new HttpClient(handler);
            client.DefaultRequestHeaders.Add("User-Agent", USER_AGENT);
            client.GetStringAsync(domain);
            cookies = ClearanceHandler._cookies;
        }

        public static void ReuploadRemoteFile(string url, string directory, string file, string title, WebClient web = null) {

            // initialize webclient if we weren't provided with one
            if (web == null) {
                web = new WebClient();
                web.Headers.Add(HttpRequestHeader.UserAgent, USER_AGENT);
            }

            // get temp path to download file to
            string localPath = Path.GetTempFileName();

            // download the original file
            try { web.DownloadFile(url, localPath); } catch (WebException wex) { ErrorLogging(wex, null, title, "Download Error: " + url); }

            // reupload file to server
            try { UploadFile(localPath, directory, file, title); } catch (Exception ex) { ErrorLogging(null, ex, title, "Upload Error: " + file); }

            // delete the file that was stored locally
            File.Delete(localPath);

        }

        public static string DownloadStringFTP(string directory) {

            WebClient request = new WebClient();

            request.Credentials = FTP_CREDENTIALS;

            try {
                byte[] newFileData = request.DownloadData("ftp://storage.bunnycdn.com" + directory);
                string fileString = System.Text.Encoding.UTF8.GetString(newFileData);

                return fileString;
            } catch (WebException e) {

                return string.Empty;
            }

        }

        public static void UploadFile(string localPath, string directory, string file, string title) {

            try {
                FtpWebRequest mkdir = GetFTPRequest("ftp://storage.bunnycdn.com" + directory, WebRequestMethods.Ftp.MakeDirectory);
                //  Console.WriteLine("url is: ftp://storage.bunnycdn.com" + directory);
                FtpWebResponse response = (FtpWebResponse)mkdir.GetResponse();
            } catch (Exception ex) {
                if (!ex.Message.Contains("directory already exists"))
                    return;
            }

            // create request to upload file
            string createURI = string.Format("ftp://storage.bunnycdn.com{0}/{1}", directory, file);
            FtpWebRequest request = GetFTPRequest(createURI, WebRequestMethods.Ftp.UploadFile);
            Console.WriteLine("[" + title + "] " + "Uploading: " + file);
            //MessageHandler.Add(title, "Uploading: " + file, ConsoleColor.White, ConsoleColor.Yellow);
            using (Stream fileStream = File.OpenRead(localPath)) {
                Stream ftpStream = null;
                while (ftpStream == null) {
                    try { ftpStream = request.GetRequestStream(); } catch (WebException ex) { ftpStream = null; }

                }
                using (ftpStream) {
                    byte[] buffer = new byte[1024 * 1024];
                    double totalReadBytesCount = 0;
                    int readBytesCount;
                    while ((readBytesCount = fileStream.Read(buffer, 0, buffer.Length)) > 0) {
                        ftpStream.Write(buffer, 0, readBytesCount);
                        totalReadBytesCount += readBytesCount;
                        double progress = (totalReadBytesCount / fileStream.Length) * 100.0;
                        // Console.Write("\rUploading {0}: {1}%   ", file, progress.ToString("F"));
                    }
                }
                Console.WriteLine("[" + title + "] " + "Successfully uploaded: " + file);
                //MessageHandler.Add(title, "Successfully uploaded: " + file, ConsoleColor.White, ConsoleColor.Yellow);
            }

        }

        public static FtpWebRequest GetFTPRequest(string uri, string method) {
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create(uri);
            request.Method = method;
            request.Credentials = FTP_CREDENTIALS;
            request.Proxy = new WebProxy();
            return request;
        }

        public static bool FileExists(string title) {
            SAFE_REQUEST.UserAgent = "jexflix-client";
            NameValueCollection values = new NameValueCollection();
            values["url"] = title;
            Response response = null;
            while (response == null) {
                try {
                    response = SAFE_REQUEST.Request("https://scraper.jexflix.com/movie_exists.php", values);
                    if (!response.status)
                        response = null;
                } catch (Exception ex) {
                    Console.WriteLine("[SafeRequest] " + ex.Message);
                }
            }
            bool exists = response.GetData<bool>("exists");
            return exists;
        }

        public static string Sanitized(this string path) {
            string invalid = "|:@";
            foreach (char c in invalid)
                path = path.Replace(c.ToString(), "");
            return path;
        }

        public static void ErrorLogging(WebException wex, Exception ex, string title, string extra = "") {
            string exception = string.Empty;

            if (wex != null) exception = wex.Message;
            if (ex != null) exception = ex.Message;

            using (StreamWriter sw = File.AppendText("error.log")) {
                sw.WriteLine("----------------------------------------");
                sw.WriteLine("[" + title + "] " + exception + " " + extra);
                sw.WriteLine("----------------------------------------");
            }
        }

        public static string JsonData(string title) {
            SAFE_REQUEST.UserAgent = "jexflix-client";
            NameValueCollection values = new NameValueCollection();
            values["title"] = title;
            Response response = SAFE_REQUEST.Request("https://scraper.jexflix.com/get_series_json.php", values);
            string URL = response.GetData<string>("url");
            return URL;
        }

        public static string GetAnimeJsonData(string title) {
            SAFE_REQUEST.UserAgent = "jexflix-client";
            NameValueCollection values = new NameValueCollection();
            values["title"] = title;
            Response response = null;
            while (response == null) {
                try {

                    response = SAFE_REQUEST.Request("https://scraper.jexflix.com/get_anime_json.php", values);

                    if (!response.status)
                        response = null;

                } catch (WebException ex) {
                    Console.WriteLine("[SafeRequest] " + ex.Message);
                    HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                    if (webResponse.StatusCode == HttpStatusCode.NotFound
                        || webResponse.StatusCode == HttpStatusCode.InternalServerError
                        || webResponse.StatusCode == HttpStatusCode.BadGateway) {
                        response = null;
                    }

                }
            }
            return response.GetData<string>("url");
        }

        /// <summary>
        /// Modified Function that handles exceptions
        /// </summary>
        public static bool BReuploadRemoteFile(string url, string directory, string file, string title, WebClient web = null, string slug = "", bool cf_bypass = false) {

            Console.WriteLine("ReUploading: " + slug + " url: " + url);

            // initialize webclient if we weren't provided with one
            if (web == null) {
                web = new WebClient();
                web.Headers.Add(HttpRequestHeader.UserAgent, Networking.USER_AGENT);
            }

            // get temp path to download file to
            string localPath = Path.GetTempFileName();

            bool success = true;


            // download the original file
            try {
                if (!cf_bypass) {
                    web.DownloadFile(url, localPath);
                } else {
                    CF_HttpClient.HttpClient_DOWNLOAD(url, localPath);
                }
            } catch (WebException wex) {

                Networking.ErrorLogging(wex, null, title, "Download Error: " + url);
                Console.WriteLine("Error downloading original file");
                success = false;
            }

            // reupload file to server
            if (success) {

                try {
                    Networking.UploadFile(localPath, directory, file, title);
                } catch (Exception ex) {
                    Networking.ErrorLogging(null, ex, title, "Upload Error: " + file);
                    Console.WriteLine("Error uploading file");
                    success = false;
                }
            } else {
                Console.WriteLine("No Success");
            }
              // delete the file that was stored locally
                File.Delete(localPath);
            return success;
        }


    }

}
