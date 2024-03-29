﻿using JexFlix_Scraper.Anime.Kitsu.IO;
using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using static JexFlix_Scraper.Anime.Kitsu.IO.KitsuAPI;
using static JexFlix_Scraper.Anime.Twist.Moe.TwistAPI;

namespace JexFlix_Scraper.Anime.Twist.Moe
{
    class Twist
    {

        /// <summary>
        /// New version of run
        /// </summary>
        public static void Run()
        {
            // Setup network stuff
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.DefaultConnectionLimit = 10000;
            ServicePointManager.MaxServicePointIdleTime = 5000;

            // Setup search Anime Details API
            Aligolia_Keys AligoliaKeys = KitsuAPI.GetAligoliaKeys();

            KitsuAnime.Anime KitsuAnimeInfo = KitsuAPI.GetKitsuAnime(AligoliaKeys, "naruto");

            Console.WriteLine(KitsuAPI.GetTitle(KitsuAnimeInfo));
        }

        public static void Run_OLD() {
            // Setup network stuff
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.DefaultConnectionLimit = 10000;
            ServicePointManager.MaxServicePointIdleTime = 5000;
            // Setup search Anime Details API
            Aligolia_Keys AligoliaKeys = KitsuAPI.GetAligoliaKeys();
            // Fetch entire list of anime data
            List<TwistAnimeData> AnimeData = TwistAPI.GetTwistAnime();

            // flips list to last videos twist.moe uploads
            // optional, comment out to disable
           // AnimeData.Reverse();

            foreach (TwistAnimeData Anime in AnimeData) {

               // if (Anime.ongoing == 0)
                 //   continue;

                //if (Anime.title.ToLower().Contains("naruto"))
                //    continue;

                //if (Anime.title.ToLower().Contains("one piece"))
                //    continue;

                //if (Anime.title.ToLower().Contains("bleach"))
                //    continue;

                //if (Anime.title.ToLower().Contains("hunter x hunter") && !Anime.title.ToLower().Contains("2011"))
                //    continue;

                // refactored blacklist to function
                if (ShouldSkip(Anime.title))
                    continue;

                // Setup Object used to upload.
                JexUpload UploadData = new JexUpload();
                UploadData.episodeData = new List<EpisodeData>();
                // Setup new API infomation
                KitsuAnime.Anime KitsuAnimeInfo = KitsuAPI.GetKitsuAnime(AligoliaKeys, Anime.title);

                // Skip animes we can't update!
                if (KitsuAnimeInfo == null) {
                    MessageHandler.Add(Anime.title, "Skipping! Fail to fetch Kitsu Information \n", ConsoleColor.Red, ConsoleColor.White);
                    continue;
                } else
                    MessageHandler.Add(Anime.title + " | Kitsu: " + KitsuAPI.GetTitle(KitsuAnimeInfo), "Now Scraping! \n", ConsoleColor.Magenta, ConsoleColor.White);

                // Get episode with slug
                List<EpisodeInfo> TwistEpisodes = TwistAPI.GetTwistEpisodes(Anime.slug.slug);

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
                                continue;
                            }
                            // Well if we haven't skipped.
                            if (AniUploadData.episodeData.Count >= 1) {
                                UploadData = AniUploadData;
                            }
                        } catch (Exception ex) {
                            Console.WriteLine("[TwistAPI] " + ex.Message);
                        }
                    } else {
                        Console.WriteLine("[TwistAPI] " + "Failed to get FTP json");
                    }
                } else {
                    Console.WriteLine("[TwistAPI] " + "No Database Response");
                    UploadData.url = KitsuAPI.GetSlug(KitsuAnimeInfo);
                }

                try {
                    Networking.ReuploadRemoteFile(KitsuAPI.GetPoster(KitsuAnimeInfo), "/anime/" + UploadData.url, "poster.jpg", UploadData.title, General.GetWebClient());
                } catch (Exception ex) {
                    Console.WriteLine("[Poster Upload] " + ex.Message);
                }
                UploadData.poster = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "poster.jpg";

                try {
                    Networking.ReuploadRemoteFile(KitsuAPI.GetCover(KitsuAnimeInfo), "/anime/" + UploadData.url, "cover.jpg", UploadData.title, General.GetWebClient());
                } catch (Exception ex) {
                    Console.WriteLine("[Cover Upload] " + ex.Message);
                }
                UploadData.cover = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "cover.jpg";

                UploadData.title = KitsuAPI.GetTitle(KitsuAnimeInfo);
                UploadData.synopsis = KitsuAPI.GetSynopsis(KitsuAnimeInfo);
                UploadData.episode_length = Convert.ToInt32(KitsuAPI.EpisodeDuration(KitsuAnimeInfo));

                // Iterate each episode
                foreach (EpisodeInfo TwistEp in TwistEpisodes) {

                    bool need_skip = false;
                    // Copy the list
                    List<EpisodeData> EpisodeCopy = new List<EpisodeData>(UploadData.episodeData);
                    // check if the list has the current episode
                    int remove_index = 0;
                    foreach (EpisodeData ep_data in UploadData.episodeData) {
                        //We Found our ep
                        if (ep_data.episode == TwistEp.number) {
                            if (ep_data.qualities.Count() >= 1) {
                                need_skip = true;
                                break;
                            } else {
                                // we across an empty cell.
                                // Fix it then fix json
                                EpisodeCopy.RemoveAt(remove_index);
                                break;
                            }
                        }
                        remove_index++;
                    }


                    // Sort the list and assign it
                    UploadData.episodeData = GetAscending(EpisodeCopy);

                    // Only upload if we don't have.
                    bool FoundEpisode = false;
                    bool HasDeleted = false;
                    EpisodeCopy = new List<EpisodeData>(UploadData.episodeData);
                    remove_index = 0;
                    foreach (EpisodeData ep_data in UploadData.episodeData) {
                        // Found our episode and it has qualities
                        if (ep_data.episode == TwistEp.number && ep_data.qualities.Count() >= 1) {
                            // If we already found one, delete extras.
                            if (FoundEpisode) {
                                EpisodeCopy.RemoveAt(remove_index);
                                HasDeleted = true;
                            }
                            FoundEpisode = true;
                            Console.WriteLine("An episode has been found");
                        }
                        remove_index++;
                    }
                    // Sort the list and assign it
                    UploadData.episodeData = GetAscending(EpisodeCopy);

                    if (!HasDeleted && need_skip) {
                        Console.WriteLine("Skiping episdode: " + TwistEp.number.ToString());
                        continue;
                    }

                    if (!FoundEpisode) {

                        MessageHandler.Add(UploadData.title, "Episode: " + TwistEp.number.ToString() + "\n", ConsoleColor.Blue, ConsoleColor.White);

                        EpisodeData EpData = new EpisodeData();
                        EpData.qualities = new List<Quality>();
                        EpData.episode = TwistEp.number;
                        EpData.episode_title = KitsuAPI.GetEpisodeTitle(KitsuAnimeInfo, TwistEp.number);
                        EpData.air_date = KitsuAPI.GetEpisodeAir(KitsuAnimeInfo, TwistEp.number);

                        string VideoUrl = TwistAPI.GetVideoLink(TwistEp.source);

                        REUPLOAD:
                        try {
                            Quality quality = new Quality();
                            quality.resolution = 1080;
                            if (Networking.BReuploadRemoteFile(VideoUrl, "/anime/" + UploadData.url + "/" + TwistEp.number.ToString(), 1080 + ".mp4", UploadData.title, General.GetWebClient(), KitsuAPI.GetSlug(KitsuAnimeInfo))) {
                                // anime has been uploaded
                                // add this to qualitity list
                                EpData.qualities.Add(quality);
                            }
                        } catch (Exception ex) {
                            Console.WriteLine("[Anime Re-Upload Error] " + ex.Message);
                            goto REUPLOAD;
                        }
                        UploadData.episodeData.Add(EpData);
                    } else {
                        UploadData.episodeData[TwistEp.number - 1].episode_title = KitsuAPI.GetEpisodeTitle(KitsuAnimeInfo, TwistEp.number);
                        UploadData.episodeData[TwistEp.number - 1].air_date = KitsuAPI.GetEpisodeAir(KitsuAnimeInfo, TwistEp.number);
                    }


                    // sort list before uploading
                    EpisodeCopy = new List<EpisodeData>(UploadData.episodeData);
                    UploadData.episodeData = GetAscending(EpisodeCopy);

                    string localPath = General.GetTempFileName();

                    //open file stream
                    using (StreamWriter file = File.CreateText(localPath)) {
                        try {
                            JsonSerializer serializer = new JsonSerializer();
                            //serialize object directly into file stream
                            serializer.Serialize(file, UploadData);
                        } catch (Exception ex) {
                            Networking.ErrorLogging(null, ex, UploadData.url, "Error Serialising Data");
                        }
                    }

                    // Upload the file to the CDN
                    retry_json:
                    try {
                        Networking.UploadFile(localPath, "/anime/" + UploadData.url, UploadData.url + ".json", UploadData.title);
                    } catch (Exception ex) {
                        Console.WriteLine(ex.Message + " JSON CDN ERROR");
                        Networking.ErrorLogging(null, ex, "Json CDN exception");
                        goto retry_json;
                    }
                    // purge the file
                    string res = PurgeCDNJson(UploadData.url);
                    Console.WriteLine("[Json Purge] " + res);

                    // Now update the link
                    string CDNLink = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + UploadData.url + ".json";

                    // Remove the file
                    File.Delete(localPath);

                    AniDb dbinfo = new AniDb();
                    dbinfo.name = UploadData.title;
                    dbinfo.url = UploadData.url;
                    dbinfo.thumbnail = UploadData.poster;
                    dbinfo.episode_data = CDNLink;
                    List<string> Kitsusynonyms = KitsuAPI.GetSynonyms(KitsuAnimeInfo);
                    if (!Kitsusynonyms.Any())
                        Kitsusynonyms.Add("");
                    dbinfo.similar = JsonConvert.SerializeObject(Kitsusynonyms);
                    List<string> KitsuGenres = KitsuAPI.GetGenres(KitsuAnimeInfo);
                    if (!KitsuGenres.Any())
                        KitsuGenres.Add("");
                    dbinfo.genres = JsonConvert.SerializeObject(KitsuGenres);
                    dbinfo.rating = KitsuAPI.GetRating(KitsuAnimeInfo);
                    dbinfo.release = KitsuAPI.GetAirDate(KitsuAnimeInfo);
                    dbinfo.duration = KitsuAPI.EpisodeDuration(KitsuAnimeInfo);
                    dbinfo.age_class = KitsuAPI.GetAgeClass(KitsuAnimeInfo);
                    dbinfo.cover = UploadData.cover;

                    // Update the database.
                    using (WebClient Web = General.GetWebClient()) {
                        try {
                            string to_upload = JsonConvert.SerializeObject(dbinfo);
                            // Console.WriteLine(to_upload);
                            Web.UploadString("https://bytesurf.io/scraper/add_anime.php", to_upload);
                            Console.WriteLine("Updated the database");

                        } catch (Exception ex) {
                            Console.WriteLine("Error submitting database info " + ex.Message);
                            Networking.ErrorLogging(null, ex, dbinfo.name, "Error Updating Database");
                        }
                    }

                    System.Threading.Thread.Sleep(100);
                }


            }
        }

        // I can use a sorting algorithm but no.
        // https://stackoverflow.com/questions/3062513/how-can-i-sort-generic-list-desc-and-asc
        public static List<EpisodeData> GetAscending(List<EpisodeData> UnsortedList) {
            return UnsortedList.OrderBy(x => x.episode).ToList();
        }

        /// <summary>
        /// Function that makes a request to server to purge the json from cdn
        /// using API written by justin
        /// </summary>     
        public static string PurgeCDNJson(string slug) {
            string request_url = string.Format("https://cdn.bytesurf.io/anime/{0}/{1}.json", slug, slug);
            // Make a request for the scraper
            string request = "https://bytesurf.io/scraper/anti_cache.php?p=" + request_url;
            try {
                return General.GET(request);
            } catch (Exception ex) {
                Console.WriteLine("[PurgeCDNFile] " + ex.Message);
            }
            return "";
        }


        /// <summary>
        /// Function that checks if the title passed through should be skipped
        /// This title will be compared recursively against an array of blacklisted titles and whitelisted titles
        /// </summary>
        public static bool ShouldSkip(string title) {
            string gtitle = title.ToLower();
            // declare predefined blacklist
            string[] blacklist = { "naruto", "one piece", "bleach", "hunter x hunter" };
            // a whitelist that should be compared against incase of recurring titles that we want
            string[] whitelist = { "2011" };
            foreach (string btitle in blacklist) {
                foreach (string wtitle in whitelist) {
                    if (gtitle.Contains(btitle) && !gtitle.Contains(wtitle)) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
}
