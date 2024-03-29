﻿using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Series {
    class UploadClasses {

        public class SeriesData {
            public string title = string.Empty;
            public string url = string.Empty;
            public string description = string.Empty;
            public string dataurl = string.Empty;
            public double? rating = 0.0;
            public string thumbnail = string.Empty;
            public string preview = string.Empty;
            public List<string> genres;
            public string imdb_id = string.Empty;
            public int? year = 0;
            public string certification = string.Empty;
        }

        public class SeriesList {
            public List<SeasonData> seasons = new List<SeasonData>();
        }

        public class SeasonData {
            public string title = string.Empty;
            public int season = 0;
            public string description = string.Empty;
            public int? released = 0;

        }

        public class EpisodeList {
            public List<Episode> episodes = new List<Episode>();
        }

        public class Episode {
            public int episode = 0;
            public string title = string.Empty;
            public string description = string.Empty;
            public int released = 0;

        }

        public class EpisodeInfo {
            public int episode = 0;
            public string title = string.Empty;
            public string description = string.Empty;
            public int released = 0;
            public List<Qualities> qualities = new List<Qualities>();
            public List<Subs> subs = new List<Subs>();
        }

        public class Qualities {
            public int resolution;
        }

        public class Subs {
            public string language;
            public string url;
        }
    }
}
