using CloudFlareUtilities;
using JexFlix_Scraper.Anime.Misc;
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
                    Console.WriteLine("Scraping " + data.title_en_jp);

                    for (int ep = 1; ep <= data.episode_count; ep++) {
                        string EpisodeLink = DarkSearch.GenerateAnimeEpisode(data.slug, ep);
                        Console.WriteLine(EpisodeLink);
                        string Raw = CF_HttpClient.HttpClient_GETAsync(EpisodeLink);
                        List<DarkMirror> mirrors = DarkSearch.GenerateMirrors(Raw);

                        if (mirrors != null) {
                            foreach (DarkMirror mirror in mirrors)
                                Console.WriteLine(mirror.video_url);
                        }

                        System.Threading.Thread.Sleep(1000);
                    }

                }

            }
        }
    }

}

