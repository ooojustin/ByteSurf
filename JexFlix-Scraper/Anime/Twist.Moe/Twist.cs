using JexFlix_Scraper.Anime.Kitsu.IO;
using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using static JexFlix_Scraper.Anime.Kitsu.IO.KitsuAPI;
using static JexFlix_Scraper.Anime.Twist.Moe.TwistAPI;

namespace JexFlix_Scraper.Anime.Twist.Moe {
    class Twist {

        public static void Run() {
            // Setup network stuff
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.DefaultConnectionLimit = 10000;
            ServicePointManager.MaxServicePointIdleTime = 5000;
            // Setup search Anime Details API
            Aligolia_Keys AligoliaKeys = KitsuAPI.GetAligoliaKeys();

            // Fetch entire list of anime data
            List<TwistAnimeData> AnimeData = TwistAPI.GetTwistAnime();

            foreach (TwistAnimeData Anime in AnimeData) {

                // Setup Object used to upload.
                JexUpload UploadData = new JexUpload();
                UploadData.episodeData = new List<EpisodeData>();

                // Setup new API infomation
                KitsuAnime.Anime KitsuAnimeInfo = KitsuAPI.GetKitsuAnime(AligoliaKeys, Anime.title);

                // Skip animes we can't update!
                if (KitsuAnimeInfo == null)
                    MessageHandler.Add(Anime.title, "Skipping! Fail to fetch Kitsu Information \n", ConsoleColor.Red, ConsoleColor.White);
                else
                    MessageHandler.Add(Anime.title, "Now Scraping! \n", ConsoleColor.Magenta, ConsoleColor.White);

                // Get episode with slug
                List<EpisodeInfo> TwistEpisodes = TwistAPI.GetTwistEpisodes(Anime.slug.slug);

                // Iterate each episode
                foreach (EpisodeInfo TwistEp in TwistEpisodes) {

                    // Grab the existing json link from the database
                    string JsonResponse = Networking.GetAnimeJsonData(KitsuAPI.GetSlug(KitsuAnimeInfo));
                    // Check and compare the json if we got a link
                    if (!string.IsNullOrEmpty(JsonResponse)) {
                        // If we have some content. Visit the link and get the json response
                        string ftp_response = JsonResponse.Substring(Networking.CDN_URL.Length, JsonResponse.Length - Networking.CDN_URL.Length);
                        string raw_json = Networking.DownloadStringFTP(ftp_response);
                        if (!string.IsNullOrEmpty(raw_json)) {
                            try {
                                JexUpload AniUploadData = JsonConvert.DeserializeObject<JexUpload>(raw_json, General.DeserializeSettings);
 
                                // We also need to skip every episode we have already...
                                if (AniUploadData.episodeData.Count() >= TwistEpisodes.Count()) {
                                    Console.WriteLine("Skiping " + AniUploadData.url);
                                    // continue;
                                }

                                // Well if we haven't skipped.
                                if (AniUploadData.episodeData.Count >= 1) {
                                    UploadData = AniUploadData;
                                }

                            } catch (Exception ex) {
                                Console.WriteLine("[DarkAPI] " + ex.Message);
                            }
                        } else {
                            Console.WriteLine("[DarkAPI] " + "Failed to get FTP json");
                        }
                    } else {
                        Console.WriteLine("[DarkAPI] " + "No Database Response");

                        UploadData.url = data.slug;
                    }

                }
            }
        }
    }
}
