using JexFlix_Scraper.Flixify;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using static JexFlix_Scraper.Series.PageClasses;

namespace JexFlix_Scraper.Shows {
    class Series {

        private static CookieContainer Cookies = null;

        private const string FLIXIFY = "https://flixify.com/";
        private const string SHOW_URL = FLIXIFY + "/shows?_t=nu7m7a&_u=ji9joxc5ip&add_mroot=1&description=1&o=t&p={0}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=shows";

        public static void Run() {

            CookieAwareWebClient web = new CookieAwareWebClient();
            Networking.BypassCloudFlare(FLIXIFY + "/login", out Cookies);

            // initialize request headers
            web.Cookies = Cookies;
            web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            web.Headers.Add("Accept-Encoding", "gzip, deflate, br");
            web.Headers.Add("Accept-Language", "en-US,en;q=0.9,ja;q=0.8");

            // establish post data
            NameValueCollection values = new NameValueCollection();
            values["ref"] = "";
            values["email"] = "nex@weebware.net";
            values["password"] = "fuckniggers69";

            // send request to store cookies from valid login
            web.UploadValues(FLIXIFY + "/login", values);

            InitializeScraper(web);
        }

        public static void InitializeScraper(CookieAwareWebClient web) {
            // 100 pages, if theres more than 3000 results idc nobody is watching 90% of these shows anyways.

            for (int page = 1; page <= 100; page++) {
                web.FlixifyHeaders();
                byte[] response = null;

                try {
                    string url = string.Format(SHOW_URL, page);
                    response = web.DownloadData(url);
                }
                catch (WebException ex) {
                    // catch NotFound exception
                    // continue to next genre (out of videos)
                    HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                    if (webResponse.StatusCode == HttpStatusCode.NotFound)
                        break;
                }
                string raw = Encoding.Default.GetString(response);
                Console.WriteLine(raw);
                Console.ReadKey();
                Parse(raw, web);
            }

        }

        public static void Parse(string raw, CookieAwareWebClient web) {
            RootObject pageData = JsonConvert.DeserializeObject<RootObject>(raw);

            foreach (Item item in pageData.items) {
                Console.WriteLine(item.url);
            }
        }

    }
}
