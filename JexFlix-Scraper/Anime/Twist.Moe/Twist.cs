using JexFlix_Scraper.Anime.Kitsu.IO;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using static JexFlix_Scraper.Anime.Kitsu.IO.KitsuAPI;

namespace JexFlix_Scraper.Anime.Twist.Moe {
    class Twist {

        public static void Run() {

            Aligolia_Keys AligoliaKeys = KitsuAPI.GetAligoliaKeys();

            Media_Production Media = KitsuAPI.GetMediaProduction(AligoliaKeys, "naruto");

            foreach (Hit hit in Media.hits) {
                Console.WriteLine(hit.slug);
            }
        }
    }
}
