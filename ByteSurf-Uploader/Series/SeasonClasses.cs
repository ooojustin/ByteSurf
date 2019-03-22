using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Series {
    class SeasonClasses {

        public class Images {
            public string preview { get; set; }
            public string poster { get; set; }
            public string preview_large { get; set; }
            public List<object> previews { get; set; }
        }

        public class Item {
            public string id { get; set; }
            public string type { get; set; }
            public string url { get; set; }
            public string title { get; set; }
            public string description { get; set; }
            public int? duration { get; set; }
            public int? year { get; set; }
            public DateTime released { get; set; }
            public int? released_sec_ago { get; set; }
            public string lang { get; set; }
            public string certification { get; set; }
            public List<object> keywords { get; set; }
            public List<string> genres { get; set; }
            public double? rating { get; set; }
            public string imdb_id { get; set; }
            public object blocked_by { get; set; }
            public int last_seq { get; set; }
            public Images images { get; set; }
        }

        public class Images2 {
            public object preview { get; set; }
            public object poster { get; set; }
            public string preview_large { get; set; }
            public object preview_grid { get; set; }
            public object preview_list { get; set; }
            public List<object> previews { get; set; }
        }

        public class SeasonItem {
            public string id { get; set; }
            public string type { get; set; }
            public string url { get; set; }
            public string title { get; set; }
            public string description { get; set; }
            public int? duration { get; set; }
            public DateTime? released { get; set; }
            public int? released_sec_ago { get; set; }
            public object year { get; set; }
            public int? seq { get; set; }
            public object seq2 { get; set; }
            public Images2 images { get; set; }
            public bool available { get; set; }
        }

        public class SeasonObject {
            public Item item { get; set; }
            public List<SeasonItem> seasons { get; set; }
        }
    }
}
