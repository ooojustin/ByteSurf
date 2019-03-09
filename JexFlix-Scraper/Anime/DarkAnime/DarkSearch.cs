using CloudFlareUtilities;
using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.DarkAnime {

    public class DarkSearch {

        public const string DARKSTREAM = "https://darkanime.stream";
        public const string ANIME_API = "https://darkanime.stream/api/animes";
        public const string ANIME_LINK = "https://darkanime.stream/animes/{0}";
        public const string EPISODE_LINK = "https://darkanime.stream/animes/{0}/episodes/{1}";

        /// <summary>
        /// Makes a request to darkanime and fetches the all anime json and converts it to the darkapi
        /// </summary>
        public static DarkAPI GetDarkAPI(string url = ANIME_API ) {
            try {
                string Response = CF_HttpClient.HttpClient_GETAsync(url);
                DarkAPI des =  JsonConvert.DeserializeObject<DarkAPI>(Response);
                return des;
            } catch (Exception ex) {
                Console.WriteLine("[DarkAPI] " + ex.Message);
                return null;
            }
        }

        public static string GenerateAnimeLink(string slug) {
            return string.Format(ANIME_LINK, slug);
        }

        public static string GenerateAnimeEpisode(string slug, int episode) {
            return string.Format(EPISODE_LINK, slug, episode.ToString());
        }

    }

}