using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime {

    public class AniUpload {
        public string title;
        public string synopsis;
        public string preview;
        public string thumbnail;
        public string url;
        public int episode_length;
        public List<string> genres = new List<string>();
        public List<EpisodeData> episodeData = new List<EpisodeData>();
    }

    public class Quality {
        public int resolution;
        public string link;
    }

    public class EpisodeData {
        public string episode;
        public string description = string.Empty;
        public string thumbnail;
        public int duration;
        public List<Quality> qualities = new List<Quality>();
    }



}