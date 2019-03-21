using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.MyAnimeList {
    class MAL {
        // Depreciated 
        public const string MAL_LOOKUP = "https://myanimelist.net/search/prefix.json?type=anime&keyword={0}&v=1";

        // Use Jikan
        // Param: Query
        public const string JIKAN_SEARCH = "https://api.jikan.moe/v3/search/anime/?q={0}&page=1";
        // Param: ID
        public const string JIKAN_EPISODE = "https://myanimelist.net/search/prefix.json?type=anime&keyword={0}&v=1";
    }
}
