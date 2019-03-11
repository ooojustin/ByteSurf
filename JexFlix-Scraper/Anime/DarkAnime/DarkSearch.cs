using CloudFlareUtilities;
using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Text.RegularExpressions;
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
        public static DarkAPI GetDarkAPI(string url = ANIME_API) {
            try {
                string Response = CF_HttpClient.HttpClient_GETAsync(url);
                return JsonConvert.DeserializeObject<DarkAPI>(Response, General.DeserializeSettings);
            } catch (Exception ex) {
                Console.WriteLine("[DarkAPI] " + ex.Message);
                return null;
            }
        }

        /// <summary>
        /// Creates the link to the anime page
        /// </summary>
        public static string GenerateAnimeLink(string slug) {
            return string.Format(ANIME_LINK, slug);
        }

        /// <summary>
        /// Creates the direct episode link
        /// </summary>
        public static string GenerateAnimeEpisode(string slug, int episode) {
            return string.Format(EPISODE_LINK, slug, episode.ToString());
        }

        /// <summary>
        /// Generates the class of mirrors using regex
        /// </summary>
        public static List<DarkMirror> GenerateMirrors(string raw) {
            Regex regex = new Regex("sources='.*?'>", RegexOptions.Singleline);
            Match match = regex.Match(raw);
            if (!match.Success) return null;
            return JsonConvert.DeserializeObject<List<DarkMirror>>(match.Value.Split('\'')[1], General.DeserializeSettings);
        }

        public static int GetHighestEpisodeCount(string slug) {
            string AnimeLink = GenerateAnimeLink(slug);
            try {
                string raw = CF_HttpClient.HttpClient_GETAsync(AnimeLink);

                if (!string.IsNullOrEmpty(raw)) {
                    Regex regex = new Regex("episodes/.*?\">", RegexOptions.Singleline);
                    Match match = regex.Match(raw);
                    if (match.Success) {
                        string content = match.Value;
                        return Convert.ToInt32(content.Split('/')[1].Split('"')[0]);
                    }
                } else {
                    Console.WriteLine("No page source found");
                }
            } catch (Exception ex) {
                Console.WriteLine("[HighestEpisodeCount] " + ex.Message);
            }
            return 0;
        }
    }

}