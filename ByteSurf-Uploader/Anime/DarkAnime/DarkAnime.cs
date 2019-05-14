using CloudFlareUtilities;
using JexFlix_Scraper.Anime.MasterAnime.Scraper;
using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.DarkAnime {

    public class DarkAnime {

        public static void Run() {
            ServicePointManager.Expect100Continue = false;
            ServicePointManager.DefaultConnectionLimit = 10000;
            ServicePointManager.MaxServicePointIdleTime = 5000;

            DarkAPI InitialPage = null;

            while (InitialPage == null) {
                CF_HttpClient.SetupClient(DarkSearch.DARKSTREAM);
                InitialPage = DarkSearch.GetDarkAPI();
                System.Threading.Thread.Sleep(1000);
            }


            string queued_page = DarkSearch.ANIME_API;

            // DUMP ANIME LIST
            // using (StreamWriter sw = new StreamWriter("AnimesFound.txt")) {
            // sw.WriteLine(data.title_en_jp);
            // }

            for (int page_number = 0; page_number < InitialPage.last_page; page_number++) {

                RE_SEARCH:
                System.Threading.Thread.Sleep(1000);

                DarkAPI AnimeInfo = DarkSearch.GetDarkAPI(queued_page);

                if (AnimeInfo != null) {
                    queued_page = AnimeInfo.next_page_url;
                } else {
                    goto RE_SEARCH;
                }

                Console.WriteLine("Page: " + AnimeInfo.current_page);

                // Page skipper          
                //if (page_number < 6)
                //   continue;

                // Iterating the anime
                foreach (DarkAPI.Data data in AnimeInfo.data) {

                    JexUpload UploadData = new JexUpload();
                    UploadData.episodeData = new List<EpisodeData>();

                    int HighestEpisodeCount = DarkSearch.GetHighestEpisodeCount(data.slug);
                    Console.WriteLine("Episode count: " + HighestEpisodeCount);

                    // Grab the existing json link from the database
                    string JsonResponse = Networking.GetAnimeJsonData(data.slug);
                    // Check and compare the json if we got a link
                    if (!string.IsNullOrEmpty(JsonResponse)) {
                        // If we have some content. Visit the link and get the json response
                        string ftp_response = JsonResponse.Substring(Networking.CDN_URL.Length, JsonResponse.Length - Networking.CDN_URL.Length);
                        string raw_json = Networking.DownloadStringFTP(ftp_response);
                        if (!string.IsNullOrEmpty(raw_json)) {
                            try {
                                JexUpload AniUploadData = JsonConvert.DeserializeObject<JexUpload>(raw_json, General.DeserializeSettings);
                                //foreach (var anidata in AniUploadData.episodeData) {
                                //   Console.WriteLine("[" + AniUploadData.title + "] " + "ep: " + anidata.episode + " count: " + anidata.qualities.Count());
                                //}

                                // We also need to skip every episode we have already...
                                if (AniUploadData.episodeData.Count() >= HighestEpisodeCount) {
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

                    Console.WriteLine("Scraping " + data.title_en_jp);

                    // Fill in upload data values
                    UploadData.title = data.title_en_jp;
                    UploadData.synopsis = data.synopsis;
                    UploadData.episode_length = data.episode_length;

                    try {
                        Networking.ReuploadRemoteFile(data.poster_image_medium, "/anime/" + UploadData.url, "poster.jpg", UploadData.title, General.GetWebClient());
                    } catch (Exception ex) {
                        Console.WriteLine(ex.Message);
                    }

                    UploadData.poster = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "poster.jpg";

                    // EpData.thumbnail = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "thumbnail.jpg";
                    // EpData.duration = AnimeInfo.info.episode_length;

                    // Iterate each episode
                    for (int ep = 1; ep <= HighestEpisodeCount; ep++) {

                        bool need_skip = false;

                        // Copy the list
                        List<EpisodeData> EpisodeCopy = new List<EpisodeData>(UploadData.episodeData);
                        // check if the list has the current episode
                        int remove_index = 0;
                        foreach (EpisodeData ep_data in UploadData.episodeData) {
                            //We Found our ep
                            if (ep_data.episode == ep) {
                                // Console.WriteLine("Found the Episode: " + ep + " count: " + ep_data.qualities.Count());
                                //This cell has data
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
                            if (ep_data.episode == ep && ep_data.qualities.Count() >= 1) {

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
                            Console.WriteLine("Skiping episdode: " + ep.ToString());
                            continue;
                        }

                        if (!FoundEpisode) {

                            MessageHandler.Add(UploadData.title, "Episode: " + ep.ToString() + "\n", ConsoleColor.White, ConsoleColor.Yellow);

                            EpisodeData EpData = new EpisodeData();
                            EpData.qualities = new List<Quality>();
                            EpData.episode = ep;

                            string EpisodeLink = DarkSearch.GenerateAnimeEpisode(data.slug, ep);
                            Console.WriteLine(EpisodeLink);

                            List<DarkMirror> mirrors = null;

                            string raw_res = null;

                            while (raw_res == null) {
                                try {
                                    raw_res = CF_HttpClient.HttpClient_GetAsync(EpisodeLink).GetAwaiter().GetResult();
                                } catch {
                                    raw_res = null;
                                }
                            }

                            if (raw_res == "error")
                                continue;

                            mirrors = DarkSearch.GenerateMirrors(raw_res);


                            foreach (DarkMirror mirror in mirrors) {

                                // Works pp.
                                Action<string> callback = (s) => {
                                    // empty string indicate 404
                                    if (!string.IsNullOrEmpty(s)) {
                                        Quality quality = new Quality();
                                        quality.resolution = mirror.GetResolution();
                                        Console.WriteLine("[DarkAPI] " + "About to upload " + quality.resolution.ToString());
                                        if (Networking.BReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + ep.ToString(), mirror.GetResolution() + ".mp4", UploadData.title, General.GetWebClient(), data.slug)) {
                                            EpData.qualities.Add(quality);
                                        }
                                    }
                                };

                                REUPLOAD:
                                try {
                                    new MirrorParser(mirror, callback).Run();
                                } catch (Exception ex) {
                                    Console.WriteLine("[DarkAPI] " + ex.Message);
                                    goto REUPLOAD;
                                }

                            }
                            UploadData.episodeData.Add(EpData);
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
                        dbinfo.thumbnail = UploadData.poster;
                        dbinfo.episode_data = CDNLink;


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

                        System.Threading.Thread.Sleep(1000);
                    }

                }

            }
        }

        // I can use a sorting algorithm but no.
        // https://stackoverflow.com/questions/3062513/how-can-i-sort-generic-list-desc-and-asc
        public static List<EpisodeData> GetAscending(List<EpisodeData> UnsortedList) {
            return UnsortedList.OrderBy(x => x.episode).ToList();
        }


    }
}

