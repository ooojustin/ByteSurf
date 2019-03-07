using JexFlix_Scraper.Flixify;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using static JexFlix_Scraper.Series.EpisodeClasses;
using static JexFlix_Scraper.Series.PageClasses;
using static JexFlix_Scraper.Series.SeasonClasses;
using static JexFlix_Scraper.Series.UploadClasses;

namespace JexFlix_Scraper.Shows {
    class Shows {

        private static CookieContainer Cookies = null;

        private const string FLIXIFY = "https://flixify.com/";
        private const string SHOW_URL = FLIXIFY + "/shows?_t=nu7m7a&_u=ji9joxc5ip&add_mroot=1&description=1&o=t&p={0}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=shows";
        private const string GET_SEASONS = FLIXIFY + "{0}?_t=ijbgom&_u=ji9joxc5ip&add_mroot=1&add_sequels=1&cast=0&crew=0&description=1&episodes_list=0&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=1&slug=1";
        private const string GET_EPISODES = FLIXIFY + "{0}?_t=aml7bt&_u=ji9joxc5ip&add_mroot=1&add_sequels=1&cast=0&crew=0&description=1&episodes_list=1&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=0&slug=1 ";

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
                ParseShows(raw);
                Console.ReadKey();
            }

        }

        public static void ParseShows(string raw) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            PageObject pageData = JsonConvert.DeserializeObject<PageObject>(raw);

            foreach (PageItem item in pageData.items) {
                SeriesData series = new SeriesData();
                series.title = item.title;
                series.url = item.url.Substring(7);
                series.dataurl = Networking.CDN_URL + item.url + "/data.json";
                series.preview = Networking.CDN_URL + item.url + "/preview.jpg";
                series.thumbnail = Networking.CDN_URL + item.url + "/thumbnail.jpg";
                series.genres = item.genres;
                series.description = item.description;

                if (item.year != null)
                    series.year = item.year;

                if (item.imdb_id != null)
                    series.imdb_id = item.imdb_id;

                if (item.certification != null)
                    series.certification = item.certification;

                if (item.rating != null)
                    series.rating = item.rating;

                web.UploadString("https://scraper.jexflix.com/add_series.php", JsonConvert.SerializeObject(series));

                string path = Path.GetTempFileName();
                File.WriteAllText(path, JsonConvert.SerializeObject(series));

                Networking.UploadFile(path, item.url, "data.json", "data.json");
                ParseSeasons(item.url);
            }
        }

        public static void ParseSeasons(string series) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            string url = string.Format(GET_SEASONS, series);
            byte[] response = null;

            web.FlixifyHeaders();
            response = web.DownloadData(url);

            string raw = Encoding.Default.GetString(response);
            SeasonObject seasonData = JsonConvert.DeserializeObject<SeasonObject>(raw);

            foreach (SeasonItem season in seasonData.seasons) {
                GetEpisodes(season.url);
            }
        }

        public static void GetEpisodes(string season) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            string url = string.Format(GET_EPISODES, season);
            byte[] response = null;

            web.FlixifyHeaders();
            response = web.DownloadData(url);

            string raw = Encoding.Default.GetString(response);
            EpisodeObject episodeData = JsonConvert.DeserializeObject<EpisodeObject>(raw);

            foreach (EpisodeItem episode in episodeData.episodes) {
                
            }

        }

    }
}
