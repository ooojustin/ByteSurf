using JexFlix_Scraper.Classes;
using SafeRequest;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper {
    public static class Networking {

        public static void SetHeaders(CookieAwareWebClient web) {
            web.Headers.Add("Host", "flixify.com");
            web.Headers.Add("Accept", "application/json");
            web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            web.Headers.Add("Referer", "https://flixify.com/movies?_rsrc=chrome/newtab");
        }

        public const string BASE_IMAGES_URL = "https://a.flixify.com";
        public const string BASE_URL = "https://flixify.com";
        public static void DownloadFiles(RootObject data) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            // create directory to download the files to, we will delete this later.
            string directory = SanatizePathName(data.item.title);
            if(!Directory.Exists(directory)) Directory.CreateDirectory(directory);

            string preview_url = BASE_IMAGES_URL + data.item.images.preview_large;
            string thumbnail_url = BASE_IMAGES_URL + data.item.images.poster;

            Console.WriteLine("Downloading " + data.item.title + " preview...");
            if (!File.Exists(directory + "/preview.jpg")) web.DownloadFile(preview_url, directory + "/preview.jpg");
            Console.WriteLine("Completed.");
            Console.WriteLine("Downloading " + data.item.title + " thumbnail...");
            if (!File.Exists(directory + "/thumbnail.jpg")) web.DownloadFile(thumbnail_url, directory + "/thumbnail.jpg");
            Console.WriteLine("Completed.");

            if (data.item.download.download_720 != null) {
                Console.WriteLine("Downloading " + data.item.title + " in 720p...");
                if (!File.Exists(directory + "/720.mp4")) web.DownloadFile(BASE_URL + data.item.download.download_720, directory + "/720.mp4");
                Console.WriteLine("Completed.");
            }
            if (data.item.download.download_1080 != null) {
                Console.WriteLine("Downloading " + data.item.title + " in 1080p...");
                if (!File.Exists(directory + "/1080.mp4")) web.DownloadFile(BASE_URL + data.item.download.download_1080, directory + "/1080.mp4");
                Console.WriteLine("Completed.");
            }
        }


        public static void UploadFiles(RootObject data) {
            string directory = data.item.url;
            DirectoryInfo d = new DirectoryInfo(SanatizePathName(data.item.title));
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

                using (Stream fileStream = File.OpenRead(Directory.GetCurrentDirectory() + "\\" + SanatizePathName(data.item.title) + "\\" + file.Name))
                using (Stream ftpStream = request.GetRequestStream()) {
                    byte[] buffer = new byte[1024 * 1024];
                    int totalReadBytesCount = 0;
                    int readBytesCount;
                    while ((readBytesCount = fileStream.Read(buffer, 0, buffer.Length)) > 0) {
                        ftpStream.Write(buffer, 0, readBytesCount);
                        totalReadBytesCount += readBytesCount;
                        Double progress = totalReadBytesCount * 100.0 / fileStream.Length;
                        Console.Write("\rUploading {0}: {1}%   ", file.Name, (int)progress);
                    }
                }
                Console.WriteLine("Successfully uploaded: " + file.Name);
            }
        }

        public static string ENCRYPTION_KEY = "jexflix";
        public static SafeRequest.SafeRequest safeRequest = new SafeRequest.SafeRequest(ENCRYPTION_KEY);

        public static Response CheckFileExists(string title) {
            safeRequest.UserAgent = "jexflix-client";

            NameValueCollection values = new NameValueCollection();
            values["title"] = title;

            return safeRequest.Request("https://scraper.jexflix.com/movie_exists.php", values);
        }
         
        public static string SanatizePathName(string path) {
            string invalid = "|:@";

            foreach (char c in invalid) {
                path = path.Replace(c.ToString(), "");
            }
            return path;
        }
    }
}
