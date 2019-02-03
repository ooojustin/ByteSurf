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

        public static void UploadFile(string name, string path) {
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create("ftp://storage.bunnycdn.com/movies/" + "test.txt");
            request.Credentials = new NetworkCredential("jexflix", "ce726c9e-edcc-4adb-839edc6148bb-7807-4e03");
            request.Method = WebRequestMethods.Ftp.UploadFile;

            using (Stream fileStream = File.OpenRead(@"test.txt"))
            using (Stream ftpStream = request.GetRequestStream()) {
                byte[] buffer = new byte[10240];
                int read;
                while ((read = fileStream.Read(buffer, 0, buffer.Length)) > 0) {
                    ftpStream.Write(buffer, 0, read);
                    Console.WriteLine("Uploaded {0} bytes", fileStream.Position);
                }
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
    }
}
