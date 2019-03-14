using CloudFlareUtilities;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Misc {

    class CF_HttpClient {

        private static ClearanceHandler handler = new ClearanceHandler();

        private static HttpClient http_client;

        public static void SetupClient() {
            handler = new ClearanceHandler();
            http_client = new HttpClient(handler);
            http_client.GetStringAsync(DarkAnime.DarkSearch.ANIME_LINK);
        }

        static public async Task<string> HttpClient_GetAsync(string path) {
            string resp = null;
            HttpResponseMessage response = await http_client.GetAsync(path);
            if (response.IsSuccessStatusCode) {
                resp = await response.Content.ReadAsStringAsync();
            } else if (response.StatusCode == HttpStatusCode.NotFound || response.StatusCode == HttpStatusCode.InternalServerError) {
                resp = "error";
            }
            return resp;
        }


        /// <summary>
        /// Wrapper for HttpClient function for cloudflare bypass
        /// This creates a GET request
        /// </summary>
        public static string HttpClient_GET(string url) {
            try {
                return http_client.GetStringAsync(url).Result;
            } catch (WebException ex) {
                HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                if (webResponse.StatusCode == HttpStatusCode.NotFound) {
                    return "404";
                }
                return "failed";
            }
        }

        /// <summary>
        /// Copies the contents of input to output. Doesn't close either stream.
        /// </summary>
        private static void CopyStream(Stream input, Stream output) {
            byte[] buffer = new byte[8 * 1024];
            int len;
            while ((len = input.Read(buffer, 0, buffer.Length)) > 0) {
                output.Write(buffer, 0, len);
            }
        }

        /// <summary>
        /// Cloudflare Bypass function for initiating a simple download given a url and filepath
        /// </summary>
        public static void HttpClient_DOWNLOAD(string url, string file_path) {

            HttpClient http_client = new HttpClient(handler);

            HttpResponseMessage url_async = http_client.GetAsync(url).Result;

            Stream file_stream = url_async.Content.ReadAsStreamAsync().Result;

            using (Stream file = File.Create(file_path)) {
                CopyStream(file_stream, file);
            }
        }

    }

}
