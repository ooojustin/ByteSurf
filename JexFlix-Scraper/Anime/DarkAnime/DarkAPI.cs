using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.DarkAnime {

    /// <summary>
    /// This is the JSON class for the Api for DarkAnime
    /// </summary>
    public class DarkAPI {

        public int current_page { get; set; }
        public List<Data> data { get; set; }
        public string first_page_url { get; set; }
        public int from { get; set; }
        public int last_page { get; set; }
        public string last_page_url { get; set; }
        public string next_page_url { get; set; }
        public string path { get; set; }
        public int per_page { get; set; }
        public object prev_page_url { get; set; }
        public int to { get; set; }
        public int total { get; set; }


        public class Data {
            public int kitsu_id { get; set; }
            public string type { get; set; }
            public string self { get; set; }
            public string slug { get; set; }
            public string as_slug { get; set; }
            public string synopsis { get; set; }
            public string title { get; set; }
            public string title_en { get; set; }
            public string title_en_jp { get; set; }
            public string title_ja_jp { get; set; }
            public string start_date { get; set; }
            public string end_date { get; set; }
            public string age_rating { get; set; }
            public string age_rating_guide { get; set; }
            public string subtype { get; set; }
            public string status { get; set; }
            public string tba { get; set; }
            public int? episode_count { get; set; }
            public int? episode_length { get; set; }
            public string youtube_video_id { get; set; }
            public string show_type { get; set; }
            public bool nsfw { get; set; }
            public string poster_image_medium { get; set; }
            public string cover_image_small { get; set; }
            public bool ongoing { get; set; }
            public bool published { get; set; }
            public string created_at { get; set; }
            public string updated_at { get; set; }
            public string season_slug { get; set; }
        }
    }


    public class DarkSearch {

        private static CookieContainer Cookies = null;

        private const string DARKSTREAM = "https://darkanime.stream";
        private const string ANIME_API = "https://darkanime.stream/api/animes";

        /// <summary>
        /// This bypasses cloudflare and returns a cookie aware client 
        /// </summary>
        public CookieAwareWebClient BypassCloudflare(string site) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            Networking.BypassCloudFlare(site, out Cookies);
            web.Cookies = Cookies;
            web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            web.Headers.Add("Accept-Encoding", "gzip, deflate, br");
            web.Headers.Add("Accept-Language", "en-US,en;q=0.9,ja;q=0.8");
            return web;
        }

        /// <summary>
        /// Makes a request to darkanime and fetches the all anime json and converts it to the darkapi
        /// </summary>
        public DarkAPI GetAllAnime() {


            
        }


    }

}
