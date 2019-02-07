using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.MasterAnime {

    public static class AniExtensions {

        public static AniEpisode GetEpisode(this AniInfo anime, AniInfo.EpisodeData data) {
            return AniEpisode.Create(anime, data);
        }

        public static AniEpisode GetEpisode(this AniInfo anime, int episodeIndex) {
            return AniEpisode.Create(anime, anime.episodes[episodeIndex]);
        }

        public static int GetGenreIndex(this string genre) {
            switch (genre) {
                case "Action": return 57;
                case "Adventure": return 58;
                case "Cars": return 69;
                case "Comedy": return 59;
                case "Dementia": return 84;
                case "Demons": return 86;
                case "Drama": return 60;
                case "Ecchi": return 79;
                case "Fantasy": return 77;
                case "Game": return 93;
                case "Harem": return 89;
                case "Historical": return 82;
                case "Horror": return 71;
                case "Josei": return 66;
                case "Kids": return 95;
                case "Magic": return 88;
                case "Martial Arts": return 75;
                case "Mecha": return 85;
                case "Military": return 83;
                case "Music": return 90;
                case "Mystery": return 63;
                case "Parody": return 94;
                case "Police": return 72;
                case "Psychological": return 73;
                case "Romance": return 67;
                case "Samurai": return 87;
                case "School": return 78;
                case "Sci-Fi": return 61;
                case "Seinen": return 70;
                case "Shoujo": return 91;
                case "Shoujo Ai": return 92;
                case "Shounen": return 64;
                case "Shounen Ai": return 96;
                case "Slice of Life": return 68;
                case "Space": return 62;
                case "Sports": return 65;
                case "Super Power": return 76;
                case "Supernatural": return 80;
                case "Thriller": return 74;
                case "Vampire": return 81;
                case "Yaoi": return 98;
                case "Yuri": return 97;
                default: return -1;
            }
        }

    }
}
