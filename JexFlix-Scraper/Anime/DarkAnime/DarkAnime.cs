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

            CF_HttpClient.SetupClient();

            DarkAPI InitialPage = null;

            while (InitialPage == null) {
                InitialPage = DarkSearch.GetDarkAPI();
                System.Threading.Thread.Sleep(1000);
            }


            string queued_page = DarkSearch.ANIME_API;

            // DUMP ANIME LIST
            // using (StreamWriter sw = new StreamWriter("AnimesFound.txt")) {
            // sw.WriteLine(data.title_en_jp);
            // }

            for (int i = 0; i < InitialPage.last_page; i++) {

                RE_SEARCH:
                System.Threading.Thread.Sleep(1000);

                DarkAPI AnimeInfo = DarkSearch.GetDarkAPI(queued_page);

                if (AnimeInfo != null) {
                    queued_page = AnimeInfo.next_page_url;
                } else {
                    goto RE_SEARCH;
                }

                Console.WriteLine("Page: " + AnimeInfo.current_page);

                // Iterating the anime
                foreach (DarkAPI.Data data in AnimeInfo.data) {

                    JexUpload UploadData = new JexUpload();
                    UploadData.episodeData = new List<EpisodeData>();

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

                                // We also need to skip every episode we have already...
                                if (AniUploadData.episodeData.Count() >= data.episode_count) {
                                    Console.WriteLine("Skiping " + AniUploadData.url);
                                    continue;
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
                    UploadData.poster = data.poster_image_medium;
                    UploadData.episode_length = data.episode_length;

                    // Iterate each episode
                    for (int ep = 1; ep <= data.episode_count; ep++) {

                        bool need_skip = false;

                        // check if the list has the current episode
                        foreach (EpisodeData ep_data in UploadData.episodeData) {

                            if (ep_data.episode == ep) {
                                need_skip = true;
                                break;
                            }
                        }

                        if (need_skip) {
                            Console.WriteLine("Skiping episdode: " + ep.ToString());
                            continue;
                        }

                        EpisodeData EpData = new EpisodeData();
                        EpData.episode = ep;
                        string EpisodeLink = DarkSearch.GenerateAnimeEpisode(data.slug, ep);
                        Console.WriteLine(EpisodeLink);
                        string Raw = CF_HttpClient.HttpClient_GETAsync(EpisodeLink);
                        List<DarkMirror> mirrors = DarkSearch.GenerateMirrors(Raw);

                        if (mirrors != null) {

                            foreach (DarkMirror mirror in mirrors) {

                                // Works pp.
                                Action<string> callback = (s) => {

                                    // determine whether 1080 or 720
                                    Quality quality = new Quality();

                                    quality.resolution = mirror.GetResolution();

                                    if (Networking.BReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + ep.ToString(), mirror.GetResolution() + ".mp4", UploadData.title, General.GetWebClient(), data.slug)) {
                                        EpData.qualities.Add(quality);
                                    }
                                };
                                new MirrorParser(mirror, callback).Run();
                            }
                        }

                        UploadData.episodeData.Add(EpData);

                        string localPath = Path.GetTempFileName();

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
                        dbinfo.thumbnail = data.poster_image_medium;
                        dbinfo.episode_data = CDNLink;

                   
                        // Update the database.
                        using (WebClient Web = General.GetWebClient()) {

                            try {
                                string to_upload = JsonConvert.SerializeObject(dbinfo);
                                Console.WriteLine(to_upload);
                                Web.UploadString("https://scraper.jexflix.com/add_anime.php", to_upload);
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
    }

}

