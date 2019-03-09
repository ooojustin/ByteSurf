using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Series {
    class EpisodeClasses {

        public class Images {
            public object preview { get; set; }
            public object poster { get; set; }
            public string preview_large { get; set; }
            public object preview_grid { get; set; }
            public object preview_list { get; set; }
            public List<object> previews { get; set; }
        }

        public class Images2 {
            public string preview { get; set; }
            public string poster { get; set; }
            public string preview_grid { get; set; }
            public string preview_list { get; set; }
        }

        public class Mroot {
            public string id { get; set; }
            public string type { get; set; }
            public string url { get; set; }
            public string title { get; set; }
            public string description { get; set; }
            public string certification { get; set; }
            public int? duration { get; set; }
            public List<string> genres { get; set; }
            public double rating { get; set; }
            public string imdb_id { get; set; }
            public int year { get; set; }
            public DateTime released { get; set; }
            public int released_sec_ago { get; set; }
            public int last_seq { get; set; }
            public Images2 images { get; set; }
        }

        public class Item {
            public string id { get; set; }
            public string type { get; set; }
            public string url { get; set; }
            public string title { get; set; }
            public string description { get; set; }
            public int? duration { get; set; }
            public DateTime released { get; set; }
            public int released_sec_ago { get; set; }
            public object year { get; set; }
            public int seq { get; set; }
            public object seq2 { get; set; }
            public Images images { get; set; }
            public bool available { get; set; }
            public string root_id { get; set; }
            public Mroot mroot { get; set; }
        }

        public class Images3 {
            public string preview { get; set; }
            public object poster { get; set; }
            public string preview_large { get; set; }
            public string preview_grid { get; set; }
            public string preview_list { get; set; }
            public List<string> previews { get; set; }
        }

        public class Media {
            public string __invalid_name__1080 { get; set; }
            public string __invalid_name__720 { get; set; }
        }

        public class Download {
            [JsonProperty("720")]
            public string download_720 { get; set; }
            [JsonProperty("1080")]
            public string download_1080 { get; set; }
        }

        public class EpisodeItem {
            public string id { get; set; }
            public string type { get; set; }
            public string url { get; set; }
            public string title { get; set; }
            public string description { get; set; }
            public int? duration { get; set; }
            public DateTime released { get; set; }
            public int released_sec_ago { get; set; }
            public object year { get; set; }
            public int seq { get; set; }
            public object seq2 { get; set; }
            public int parent_seq { get; set; }
            public Images3 images { get; set; }
            public bool available { get; set; }
            public Media media { get; set; }
            public string media_id { get; set; }
            public string file_id { get; set; }
            public string stream_cachesrv_id { get; set; }
            public object stream_filesrv_id { get; set; }
            public Download download { get; set; }
        }

        public class EpisodeObject {
            public Item item { get; set; }
            public List<EpisodeItem> episodes { get; set; }
        }
    }
}
