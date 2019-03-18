﻿using JexFlix_Scraper.Anime.Kitsu.IO;
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

            int id = KitsuAPI.GetFirstTVID(Media);

            KitsuAnime.Anime AnimeInfo = KitsuAPI.GetKitsuAnime(id);

            Console.WriteLine(AnimeInfo.data.attributes.titles.en_jp);
            Console.WriteLine(AnimeInfo.data.attributes.synopsis);

        }
    }
}
