using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime {

    public class JexUpload {
        public string title { get; set; }
        public string synopsis { get; set; }
        public string preview { get; set; }
        public string thumbnail { get; set; }
        public string url { get; set; }
        public int episode_length { get; set; }
        public List<string> genres { get; set; }
        public List<EpisodeData> episodeData { get; set; }
    }

    public class Quality {
        public int resolution { get; set; }
        public string link { get; set; }
    }

    public class EpisodeData {
        public int episode { get; set; }
        public string title { get; set; }
        public string description { get; set; }
        public string thumbnail { get; set; }
        public int duration { get; set; }
        public List<Quality> qualities { get; set; } 
    }

    public class AniDb {
        public string name { get; set; }
        public string url { get; set; }
        public string thumbnail { get; set; }
        public string episode_data { get; set; }
        public string synonyms { get; set; }
    }

}