using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime {

    public class JexUpload {
        public string title { get; set; }
        public string synopsis { get; set; }
        public string poster { get; set; }
        public string cover { get; set; }
        public string url { get; set; }
        public int? episode_length { get; set; }
        public List<EpisodeData> episodeData { get; set; }
    }

    public class Quality {
        public int resolution { get; set; }
    }

    public class EpisodeData {
        public int episode { get; set; }
        public List<Quality> qualities { get; set; }
        public string episode_title { get; set; }
        public string air_date { get; set; }
    }

    public class AniDb {
        public string name { get; set; }
        public string url { get; set; }
        public string thumbnail { get; set; }
        public string episode_data { get; set; }
        public string similar { get; set; }
        public string genres { get; set; }
        public string rating { get; set; }
        public string release { get; set; }
        public string duration { get; set; }
        public string age_class { get; set; }
        public string cover { get; set; }

    }

}