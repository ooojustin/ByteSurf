﻿using JexFlix_Scraper.Anime.MasterAnime;
using JexFlix_Scraper.Anime.MasterAnime.Scraper;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using JexFlix_Scraper.Anime.Misc;
using System.IO;
using System.Net;

namespace JexFlix_Scraper.Anime {

    public static class Anime {

        /// <summary>
        /// Main function for executing code for the anime scraper.
        /// </summary>
        public static void Run() {

            AniSearch InitialAnime = AniSearch.GetAnime();

            List<AniSearch> AllAnime = new List<AniSearch>();

            AllAnime.Add(InitialAnime);

            Console.WriteLine("There are: " + InitialAnime.last_page + " pages");

            for (int i = 2; i <= InitialAnime.last_page; i++) {
                Console.WriteLine("On " + i + " page");
                AniSearch CurrentAnime = AniSearch.GetAnime(page: i);
                AllAnime.Add(CurrentAnime);
            }


            foreach (AniSearch animeFound in AllAnime) {

                foreach (AniSearch.Show anime in animeFound.data) {

                    JexUpload UploadData = new JexUpload();

                    //UploadData.genres = new List<string>();

                    UploadData.episodeData = new List<EpisodeData>();

                    AniInfo AnimeInfo = anime.GetAnime();

                    int latest_episode = 0;

                    // Check if we have the anime already...
                    string JsonResponse = Networking.JsonData(anime.slug);

                    // If we don't have the anime in the correct format
                    //if (string.IsNullOrEmpty(JsonResponse)) {
                    //     JsonResponse = Networking.JsonData(Slugify(anime.title));
                    //}

                    if (!string.IsNullOrEmpty(JsonResponse)) {
                        // If we have some content. Visit the link and get the json response
                        string ftp_response = JsonResponse.Substring(Networking.CDN_URL.Length, JsonResponse.Length - Networking.CDN_URL.Length);

                        //  string raw_json = Web.DownloadString(JsonResponse);
                        string raw_json = Networking.DownloadStringFTP(ftp_response);

                        // Deseralise it to the json class.
                        try {

                            JexUpload AniUploadData = JsonConvert.DeserializeObject<JexUpload>(raw_json, General.DeserializeSettings);

                            // If what server has is greater than what we have, we need to upload the new episodes...
                            // We also need to skip every episode we have already...
                            Console.WriteLine(AnimeInfo.info.title + " Our Count: " + AniUploadData.episodeData.Count() + " Their Count: " + AnimeInfo.episodes.Count());
                            if (AniUploadData.episodeData.Count() >= AnimeInfo.episodes.Count()) {
                                Console.WriteLine("Skiping " + AniUploadData.url);
                                continue;
                            }

                            // Well if we haven't skipped.
                            if (AniUploadData.episodeData.Count >= 1) { // Check if we have at least 1 episode.
                                Console.WriteLine("Loading existing data...");
                                latest_episode = AniUploadData.episodeData[AniUploadData.episodeData.Count - 1].episode;
                                UploadData = AniUploadData;
                            }
                        } catch (Exception ex) {
                            Networking.ErrorLogging(null, ex, anime.slug, "Error Converting JSON data from CDN");
                        }


                    } else {

                        Console.WriteLine("No Json Object Found");
                        foreach (AniInfo.Genre genre in AnimeInfo.genres) {
                            //   if (genre.name != null) UploadData.genres.Add(genre.name);
                        }

                        // Don't update existing slug anymore
                        UploadData.url = anime.slug;

                    }


                    UploadData.title = anime.title;

                    if (AnimeInfo.info.synopsis != null)
                        UploadData.synopsis = AnimeInfo.info.synopsis;

                    UploadData.episode_length = AnimeInfo.info.episode_length;

                    // Upload this to the CDN then change the link to that and set the link 
                    // cdn.jexflix.com/anime/anime-name-here/thumbnail.jpg 
                    // Networking.CDN_URL + rootObject.item.url + "/thumbnail.jpg";


                    try {
                        Networking.ReuploadRemoteFile(anime.GetThumbnail(), "/anime/" + UploadData.url, "thumbnail.jpg", UploadData.title, General.GetWebClient());
                    } catch (Exception ex) {
                        Console.WriteLine(ex.Message);
                    }

                    // UploadData.thumbnail = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "thumbnail.jpg";

                    try {
                        Networking.ReuploadRemoteFile(AnimeInfo.GetWallpaper(), "/anime/" + UploadData.url, "preview.jpg", UploadData.title, General.GetWebClient());
                    } catch (Exception ex) {
                        Console.WriteLine(ex.Message);
                    }
                    //  UploadData.preview = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "preview.jpg";


                    foreach (AniInfo.EpisodeData EpisodeInfo in AnimeInfo.episodes) {

                        bool need_skip = false;

                        foreach (EpisodeData data in UploadData.episodeData) {

                            if (data.episode == Convert.ToInt32(EpisodeInfo.info.episode)) {
                                need_skip = true;
                                break;
                            }
                        }

                        if (need_skip) {
                            Console.WriteLine("Skiping episdode: " + EpisodeInfo.info.episode);
                            continue;
                        }

                        MessageHandler.Add(UploadData.title, "Episode: " + EpisodeInfo.info.episode + "\n", ConsoleColor.White, ConsoleColor.Yellow);

                        // Get the anime title and send a request to the database to check if it already exists.
                        // If the anime title exists then we will continue to the next anime. repeat untill we dont have an anime and continue
                        // The upload from there

                        // Console.WriteLine("title: " + EpisodeInfo.info.title);

                        EpisodeData EpData = new EpisodeData();

                        EpData.qualities = new List<Quality>();

                        // EpData.title = EpisodeInfo.info.title;

                        EpData.episode = Convert.ToInt32(EpisodeInfo.info.episode);

                        // EpData.description = EpisodeInfo.info.description;

                        // Upload to the CDN then delete
                        // cdn.jexflix.com/anime/anime-name-here/1/thumbnail.jpg and set the link
                        Networking.ReuploadRemoteFile(AnimeInfo.GetThumbnail(Convert.ToInt32(EpisodeInfo.info.episode) - 1), "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "thumbnail.jpg", UploadData.title, General.GetWebClient());
                        // EpData.thumbnail = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "thumbnail.jpg";
                        // EpData.duration = AnimeInfo.info.episode_length;


                        // Fill the mirrors
                        AniEpisode episode = AnimeInfo.GetEpisode(EpisodeInfo);

                        if (episode != null) {

                            bool UltraHd = false;
                            bool Hd = false;
                            bool Standard = false;

                            bool HasHDQualities = false;

                            foreach (AniEpisode.Mirror mirror in episode.EmbedList) {

                                if (mirror.quality == 1080 || mirror.quality == 720)
                                    HasHDQualities = true;
                            }


                            foreach (AniEpisode.Mirror mirror in episode.EmbedList) {

                                Console.WriteLine("Looping through embed list");

                                if (MirrorParser.IsSupported(mirror)) {

                                    // Very ghetto fix to get the first mirror of a quality so we dont reupload videos of the same qualities
                                    /*
                                      Upload in this format
                                      cdn.jexflix.com/anime/anime-name-here/1/1080.mp4
                                    */

                                    if (mirror.quality == 1080 && !UltraHd) {

                                        Action<string> callback = (s) => {

                                            Quality quality = new Quality();

                                            quality.resolution = 1080;

                                            if (Networking.BReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "1080.mp4", UploadData.title, General.GetWebClient(), anime.slug)) {

                                                Console.WriteLine("Has Passed RemoteFile Upload");

                                                // Now update the link
                                                // quality.link = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "1080.mp4";

                                                EpData.qualities.Add(quality);

                                                UltraHd = true;
                                            }
                                        };
                                        retry:
                                        try {
                                            Console.WriteLine("Running Mirror Parser");

                                            // new MirrorParser(mirror, callback).Run();
                                            Console.WriteLine("Mirror Parser finished running");
                                        } catch (Exception ex) {
                                            Console.WriteLine(ex.Message);
                                            UltraHd = false;
                                            goto retry;
                                        }
                                    }


                                    if (mirror.quality == 720 && !Hd) {
                                        Action<string> callback = (s) => {
                                            Quality quality = new Quality();
                                            quality.resolution = 720;

                                            // Upload to CDN then delete.
                                            if (Networking.BReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "720.mp4", UploadData.title, General.GetWebClient(), anime.slug)) {
                                                Console.WriteLine("Has Passed RemoteFile Upload");

                                                // Now update the link
                                                // quality.link = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "720.mp4";

                                                EpData.qualities.Add(quality);

                                                Hd = true;
                                                Console.WriteLine("End of callback");
                                            }

                                        };
                                        retry:

                                        try {
                                            Console.WriteLine("Running Mirror Parser");
                                            //    new MirrorParser(mirror, callback).Run();
                                            Console.WriteLine("Mirror Parser finished running");
                                        } catch (Exception ex) {
                                            Console.WriteLine(ex.Message);
                                            Hd = false;
                                            goto retry;
                                        }
                                    }

                                    if (!HasHDQualities) {

                                        if (mirror.quality == 480 && !Standard) {

                                            Action<string> callback = (s) => {
                                                Quality quality = new Quality();
                                                quality.resolution = 480;

                                                // Upload to CDN then delete.
                                                if (Networking.BReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "480.mp4", UploadData.title, General.GetWebClient(), anime.slug)) {

                                                    Console.WriteLine("Has Passed RemoteFile Upload");
                                                    // Now update the link
                                                    // quality.link = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "480.mp4";

                                                    EpData.qualities.Add(quality);

                                                    Standard = true;
                                                    Console.WriteLine("End of callback");
                                                }

                                            };
                                            retry:
                                            try {
                                                Console.WriteLine("Running Mirror Parser");
                                                //     new MirrorParser(mirror, callback).Run();
                                                Console.WriteLine("Mirror Parser finished running");
                                            } catch (Exception ex) {
                                                Console.WriteLine(ex.Message);
                                                Standard = false;
                                                goto retry;
                                            }

                                        }
                                    }


                                }

                            }
                        }
                        Console.WriteLine("Adding EPData");
                        UploadData.episodeData.Add(EpData);
                        Console.WriteLine("Saving to path");
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

                        Console.WriteLine("Preparing to upload .json");
                        // Upload the file to the CDN
                        retry_json:
                        try {
                            Networking.UploadFile(localPath, "/anime/" + UploadData.url, UploadData.url + ".json", UploadData.title);
                        } catch (Exception ex) {
                            Console.WriteLine(ex.Message + " JSON CDN ERROR");
                            Networking.ErrorLogging(null, ex, "Json CDN exception");
                            goto retry_json;
                        }

                        Console.WriteLine("Json been uploaded");

                        // Now update the link
                        string CDNLink = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + UploadData.url + ".json";

                        // Remove the file
                        File.Delete(localPath);


                        AniDb dbinfo = new AniDb();
                        dbinfo.name = UploadData.title;
                        dbinfo.url = UploadData.url;
                        // dbinfo.thumbnail = UploadData.thumbnail;
                        dbinfo.episode_data = CDNLink;

                        List<string> synlist = new List<string>();
                        foreach (AniInfo.Synonyms syn in AnimeInfo.synonyms) {
                            synlist.Add(syn.title);
                        }
                        //  dbinfo.synonyms = JsonConvert.SerializeObject(synlist);

                        // Update the database.
                        using (WebClient Web = General.GetWebClient()) {

                            try {
                                string to_upload = JsonConvert.SerializeObject(dbinfo);
                                Console.WriteLine(to_upload);
                                Web.UploadString("https://bytesurf.io/scraper/add_anime.php", to_upload);
                                Console.WriteLine("Updated the database");

                            } catch (Exception ex) {
                                Console.WriteLine("Error submitting database info " + ex.Message);
                                Networking.ErrorLogging(null, ex, dbinfo.name, "Error Updating Database");
                            }

                        }

                    }

#if false
                    Console.WriteLine("Title: " + UploadData.title);
                    Console.WriteLine("Description: " + UploadData.synopsis);
                    Console.WriteLine("Preview: " + UploadData.preview);
                    Console.WriteLine("Thumbnail: " + UploadData.thumbnail);
                    Console.WriteLine("URL: " + UploadData.url);

                    foreach (var genre in UploadData.genres) {
                        Console.WriteLine(genre);
                    }

                    foreach (var ed in UploadData.episodeData) {

                        Console.WriteLine("Episode: " + ed.episode);
                        Console.WriteLine("Episode description: " + ed.description);
                        Console.WriteLine("Episode thumbnail: " + ed.thumbnail);
                        Console.WriteLine("Episode duration: " + AnimeInfo.info.episode_length);

                        foreach (var q in ed.qualities) {
                            Console.WriteLine("Episode link: " + q.link);
                            Console.WriteLine("Episode resolution: " + q.resolution);
                        }
                    }
                    // Move on and repeat to the text episode / anime
#endif
                }
            }


        }


        /// <summary>
        /// Fast Paste https://stackoverflow.com/questions/3275242/how-do-you-remove-invalid-characters-when-creating-a-friendly-url-ie-how-do-you
        /// </summary>
        public static string RemoveAccent(this string txt) {
            byte[] bytes = System.Text.Encoding.GetEncoding("Cyrillic").GetBytes(txt);
            return System.Text.Encoding.ASCII.GetString(bytes);
        }

        public static string Slugify(this string phrase) {
            string str = phrase.RemoveAccent().ToLower();
            str = System.Text.RegularExpressions.Regex.Replace(str, @"[^a-z0-9\s-]", ""); // Remove all non valid chars          
            str = System.Text.RegularExpressions.Regex.Replace(str, @"\s+", " ").Trim(); // convert multiple spaces into one space  
            str = System.Text.RegularExpressions.Regex.Replace(str, @"\s", "-"); // //Replace spaces by dashes
            return str;
        }
    }
}
