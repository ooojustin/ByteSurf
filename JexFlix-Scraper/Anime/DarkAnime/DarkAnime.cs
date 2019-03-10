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

                // Iterating the episodes
                foreach (DarkAPI.Data data in AnimeInfo.data) {

                    JexUpload UploadData = new JexUpload();
                    UploadData.genres = new List<string>();
                    UploadData.episodeData = new List<EpisodeData>();

                    // Grab the existing json link from the database
                    string JsonResponse = Networking.JsonData(data.slug);
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
                    }

                    Console.WriteLine("Scraping " + data.title_en_jp);

                    for (int ep = 1; ep <= data.episode_count; ep++) {
                        string EpisodeLink = DarkSearch.GenerateAnimeEpisode(data.slug, ep);
                        Console.WriteLine(EpisodeLink);
                        string Raw = CF_HttpClient.HttpClient_GETAsync(EpisodeLink);
                        List<DarkMirror> mirrors = DarkSearch.GenerateMirrors(Raw);

                        if (mirrors != null) {
                            foreach (DarkMirror mirror in mirrors) {

                                // Works pp.
                                Action<string> callback = (s) => {

                                };

                                new MirrorParser(mirror, callback).Run();
                            }
                        }

                        System.Threading.Thread.Sleep(1000);
                    }

                }

            }
        }
    }

}

