using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Classes {

    public class Images {
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
        [JsonProperty("720")]
        public string __invalid_name__720 { get; set; }
        [JsonProperty("1080")]
        public string __invalid_name__1080 { get; set; }
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
        public int duration { get; set; }
        public int year { get; set; }
        public DateTime released { get; set; }
        public int released_sec_ago { get; set; }
        public string lang { get; set; }
        public string certification { get; set; }
        public List<object> keywords { get; set; }
        public List<string> genres { get; set; }
        public double rating { get; set; }
        public string imdb_id { get; set; }
        public object blocked_by { get; set; }
        public Images images { get; set; }
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

    public class RootObject {
        public UrlItems item { get; set; }
    }
}
