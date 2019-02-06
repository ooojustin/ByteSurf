using CloudFlareUtilities;
using JexFlix_Scraper.Flixify;
using SafeRequest;
using System;
using System.Collections.Specialized;
using System.IO;
using System.Net;
using System.Net.Http;

namespace JexFlix_Scraper {

    public static class Networking {

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
        public const string USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36";

        /// <summary>
        /// Bypass CloudFlare on a specified domain.
        /// https://github.com/elcattivo/CloudFlareUtilities
        /// </summary>
        public static void BypassCloudFlare(string domain) {
            ClearanceHandler handler = new ClearanceHandler();
            HttpClient client = new HttpClient(handler);
            client.DefaultRequestHeaders.Add("User-Agent", USER_AGENT);
            client.GetStringAsync(domain);
        }


        public static void UploadFiles(RootObject data) {
            string directory = data.item.url;
            DirectoryInfo d = new DirectoryInfo(data.item.title.Sanitized());
            FileInfo[] files = d.GetFiles();
            NetworkCredential credentials = new NetworkCredential("jexflix", "ce726c9e-edcc-4adb-839edc6148bb-7807-4e03");

            try {
                FtpWebRequest create = (FtpWebRequest)WebRequest.Create("ftp://storage.bunnycdn.com" + directory);
                create.Method = WebRequestMethods.Ftp.MakeDirectory;
                create.Credentials = credentials;
                create.Proxy = new WebProxy();
                FtpWebResponse create_response = (FtpWebResponse)create.GetResponse();
            } catch (Exception ex) {
                if (ex.Message.Contains("directory already exists")) Console.WriteLine("Directory exists" + Environment.NewLine);
            }

            foreach (FileInfo file in files) {
                FtpWebRequest request = (FtpWebRequest)WebRequest.Create("ftp://storage.bunnycdn.com" + directory + "/" + file.Name);
                request.Method = WebRequestMethods.Ftp.UploadFile;
                request.Credentials = credentials;
                request.Proxy = new WebProxy();

                using (Stream fileStream = File.OpenRead(Directory.GetCurrentDirectory() + "\\" + data.item.title.Sanitized() + "\\" + file.Name))
                using (Stream ftpStream = request.GetRequestStream()) {
                    byte[] buffer = new byte[1024 * 1024];
                    int totalReadBytesCount = 0;
                    int readBytesCount;
                    while ((readBytesCount = fileStream.Read(buffer, 0, buffer.Length)) > 0) {
                        ftpStream.Write(buffer, 0, readBytesCount);
                        totalReadBytesCount += readBytesCount;
                        double progress = totalReadBytesCount * 100.0 / fileStream.Length;
                        Console.Write("\rUploading {0}: {1}%   ", file.Name, (int)progress);
                    }
                }
                Console.WriteLine("Successfully uploaded: " + file.Name);
            }
        }

        public static bool FileExists(string title) {
            SAFE_REQUEST.UserAgent = "JexFlix-Scraper";
            NameValueCollection values = new NameValueCollection();
            values["title"] = title;
            Response response = SAFE_REQUEST.Request("https://scraper.jexflix.com/movie_exists.php", values);
            bool exists = response.GetData<bool>("exists");
            return exists;
        }
         
        public static string Sanitized(this string path) {
            string invalid = "|:@";
            foreach (char c in invalid)
                path = path.Replace(c.ToString(), "");
            return path;
        }

    }

}
