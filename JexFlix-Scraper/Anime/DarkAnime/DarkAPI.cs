using Newtonsoft.Json;
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

    /// <summary>
    /// This is the structure of mirror links for the json located in the page source
    /// </summary>
    public class DarkMirror {
        public string episode_slug { get; set; }
        public string mirror_name { get; set; }
        public int mirror_number { get; set; }
        public string video_url { get; set; }

        private const string MP4UPLOAD_LINK = "https://www.mp4upload.com/{0}.html";

        public static string GetMP4UPloadLink(string embed) {
            return string.Format(MP4UPLOAD_LINK, embed);
        }
    }
}
