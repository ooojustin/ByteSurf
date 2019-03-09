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

            DarkAPI InitialPage = DarkSearch.GetDarkAPI();

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
                    // sources='[]'> regex this.

                }

            }
        }
    }

}

