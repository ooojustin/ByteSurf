using CloudFlareUtilities;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Misc {

    class CF_HttpClient {

        private static ClearanceHandler handler = new ClearanceHandler();

        private static HttpClient http_client;

        public static void SetupClient() {
            http_client = new HttpClient(handler);
        }

        /// <summary>
        /// Wrapper for HttpClient function for cloudflare bypass
        /// This creates a GET request
        /// </summary>
        public static string HttpClient_GETAsync(string url) {
            try {
                return http_client.GetStringAsync(url).Result;
            } catch {
                return "failed";
            }
        }


    }

}
