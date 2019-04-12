using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Flixify {

    public class MovieImages1 {
        public string preview { get; set; }
        public string poster { get; set; }
        public string preview_large { get; set; }
        public List<object> previews { get; set; }
    }

    public class Media {
        public string __invalid_name__720 { get; set; }
        public string __invalid_name__1080 { get; set; }
    }

    public class Download {
        [JsonProperty("480")]
        public string download_480 { get; set; }
        [JsonProperty("720")]
        public string download_720 { get; set; }
        [JsonProperty("1080")]
        public string download_1080 { get; set; }
    }

    public class Eng {
        public string id { get; set; }
        public string filename { get; set; }
        public string src { get; set; }
        public string url { get; set; }
    }

    public class Subtitles {
        public List<Eng> eng { get; set; }
    }

    public class UrlItems {
        public string id { get; set; }
        public string type { get; set; }
        public string url { get; set; }
        public string title { get; set; }
        public string description { get; set; }
        public int? duration { get; set; }
        public int? year { get; set; }
        public DateTime? released { get; set; }
        public long released_sec_ago { get; set; }
        public string lang { get; set; }
        public string certification { get; set; }
        public List<object> keywords { get; set; }
        public List<string> genres { get; set; }
        public double? rating { get; set; }
        public string imdb_id { get; set; }
        public object blocked_by { get; set; }
        public MovieImages1 images { get; set; }
        public Media media { get; set; }
        public string media_id { get; set; }
        public string file_id { get; set; }
        public string stream_cachesrv_id { get; set; }
        public object stream_filesrv_id { get; set; }
        public Download download { get; set; }
        public Subtitles subtitles { get; set; }
    }

    public class Images2 {
        public string preview { get; set; }
        public string poster { get; set; }
        public string preview_grid { get; set; }
        public string preview_list { get; set; }
    }

    public class MovieData {
        public UrlItems item { get; set; }
    }

    public class Data {
        public string title;
        public string url;
        public string description = string.Empty;
        public int? duration = 0;
        public double? rating = 0.0;
        public string thumbnail = string.Empty;
        public string preview = string.Empty;
        public List<Quality> qualities = new List<Quality>();
        public List<string> genres;
        public string imdb_id = string.Empty;
        public int? year = 0;
        public string certification = string.Empty;
    }

    public class Quality {
        public int resolution;
        public string link;
    }
}
