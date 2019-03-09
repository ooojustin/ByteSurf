using System;
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

        public class Season {


        }

        public class Episode {

        }

        public class Quality {
            public int resolution;
            public string link;
        }
    }
}
