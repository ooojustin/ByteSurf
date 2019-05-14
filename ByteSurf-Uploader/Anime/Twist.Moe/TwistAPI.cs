using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Security.Cryptography;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Twist.Moe {

    class TwistAPI {

        // Found in javascript https://twist.moe/_nuxt/1d5bd2d9ff91717fa9b4.js after decrypting it using an nice-fier and reading where it gets encrypted 
        // then trace back where the key is defined
        public const string SECRET_KEY = "k8B$B@0L8D$tDYHGmRg98sQ7!%GOEGOX27T";

        public const string API_LINK = "https://twist.moe/api/anime/";
        public const string API_EPISODE_LINK = "https://twist.moe/api/anime/{0}/sources";
        public const string VIDEO_FILE_LINK = "https://twistcdn.bunny.sh{0}";
        /// <summary>
        /// This webclient bypasses api restriction
        /// </summary>
        public static WebClient WebClientBypass() {
            WebClient webClient = new WebClient();
            webClient.Proxy = null;
            webClient.Headers.Add("user-agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            webClient.Headers.Add("x-access-token", "1rj2vRtegS8Y60B3w3qNZm5T2Q0TN2NR");
            return webClient;
        }

        /// <summary>
        /// Returns the entire list of anime
        /// </summary>
        public static List<TwistAnimeData> GetTwistAnime() {
            using (WebClient wc = WebClientBypass()) {
                try {
                    string raw = wc.DownloadString(API_LINK);
                    // Console.WriteLine(raw);
                    return JsonConvert.DeserializeObject<List<TwistAnimeData>>(raw, General.DeserializeSettings);
                } catch (WebException ex) {
                    Console.WriteLine("[GetTwistAnime] " + ex.Message);
                }
            }
            return null;
        }

        /// <summary>
        /// Returns the entire list of episodes for an anime.
        /// </summary>
        public static List<EpisodeInfo> GetTwistEpisodes(string slug) {
            using (WebClient wc = WebClientBypass()) {
                try {
                    string raw = wc.DownloadString(string.Format(API_EPISODE_LINK, slug));
                    return JsonConvert.DeserializeObject<List<EpisodeInfo>>(raw, General.DeserializeSettings);
                } catch (WebException ex) {
                    Console.WriteLine("[GetTwistEpisodes] " + ex.Message);
                }
            }
            return null;
        }

        public static string GetVideoLink(string source) {
            AESCrypto crypto = new AESCrypto();
            return string.Format(VIDEO_FILE_LINK, crypto.OpenSSLDecrypt(source, SECRET_KEY)).Replace(" /", "/");
        }

        public class EpisodeInfo {
            public int id { get; set; }
            public string source { get; set; }
            public int number { get; set; }
            public int anime_id { get; set; }
            public string created_at { get; set; }
            public string updated_at { get; set; }
        }

        public class Slug {
            public int id { get; set; }
            public string slug { get; set; }
            public int anime_id { get; set; }
            public string created_at { get; set; }
            public string updated_at { get; set; }
        }

        public class TwistAnimeData {
            public int id { get; set; }
            public string title { get; set; }
            public string alt_title { get; set; }
            public int season { get; set; }
            public int ongoing { get; set; }
            public int? hb_id { get; set; }
            public string created_at { get; set; }
            public string updated_at { get; set; }
            public int hidden { get; set; }
            public int? mal_id { get; set; }
            public Slug slug { get; set; }
        }
    }
}
