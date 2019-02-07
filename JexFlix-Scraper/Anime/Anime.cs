using JexFlix_Scraper.Anime.MasterAnime;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

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


            // Dumps out all the anime that exists.
            using (System.IO.StreamWriter file = new System.IO.StreamWriter("AnimeFound.txt", true)) {

                foreach (AniSearch animeFound in AllAnime) {

                    foreach (AniSearch.Show i in animeFound.data) {

                        // i.GetAnime();

                        Console.WriteLine(i.title);

                        file.WriteLine(i.title);


                    }
                }
            }

           //  Console.ReadKey();

        }


    }



}