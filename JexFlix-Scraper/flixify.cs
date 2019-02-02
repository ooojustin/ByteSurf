using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper {

    public class ServerData {
        public List<Item> items { get; set; }
        public List<Filter> filters { get; set; }
        public int total { get; set; }
        public int page { get; set; }
        public int items_per_page { get; set; }
        public string ts { get; set; }
    }

    public class Images {
        public string preview { get; set; }
        public string poster { get; set; }
        public string preview_list { get; set; }
        public string preview_grid { get; set; }
    }

    public class Item {
        public string id { get; set; }
        public string type { get; set; }
        public string url { get; set; }
        public string title { get; set; }
        public string description { get; set; }
        public string slug { get; set; }
        public string certification { get; set; }
        public int duration { get; set; }
        public List<string> genres { get; set; }
        public double? rating { get; set; }
        public string imdb_id { get; set; }
        public int year { get; set; }
        public DateTime released { get; set; }
        public long released_sec_ago { get; set; }
        public Images images { get; set; }
    }

    public class Item2 {
        public string url { get; set; }
        public object @class { get; set; }
        public string title { get; set; }
        public string active { get; set; }
        public string type { get; set; }
        public string value { get; set; }
    }

    public class Filter {
        public string url { get; set; }
        public string @class { get; set; }
        public string title { get; set; }
        public string param { get; set; }
        public string value { get; set; }
        public string type { get; set; }
        public string active { get; set; }
        public List<Item2> items { get; set; }
    }
}
