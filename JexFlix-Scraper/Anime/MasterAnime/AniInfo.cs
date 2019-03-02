using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.MasterAnime {

    public class AniInfo {


        /// <summary>
        /// URL to receive information about a show (based on ID) as json.
        /// </summary>
        private const string DETAIL_URL = "https://www.masterani.me/api/anime/{0}/detailed";
        private const string THUMBNAIL_URL = "https://cdn.masterani.me/episodes/{0}";
        private const string WALLPAPER_URL = "https://cdn.masterani.me/wallpaper/1/{0}";

        public string GetWallpaper() {
            if (wallpapers.Any())
                return string.Format(WALLPAPER_URL, wallpapers[0].file);
            else return string.Empty;
        }

        /// <summary>
        /// Gets an object representing a specified anime from a unique ID.
        /// </summary>
        public static AniInfo GetAnime(int id) {

            string url = string.Format(DETAIL_URL, id);
            string json = General.GET(url);

            if (json.StartsWith("ERROR"))
                return null;

            return JsonConvert.DeserializeObject<AniInfo>(json, General.DeserializeSettings);

        }

        /// <summary>
        /// Gets a random anime from the site.
        /// </summary>
        public static AniInfo GetRandomAnime() {
            string animeUrl = General.RedirectedURL("https://www.masterani.me/anime/random");
            string strID = animeUrl.Split('/').Last().Split('-').First();
            int id = Convert.ToInt32(strID);
            return GetAnime(id);
        }

        /// <summary>
        /// Gets a link to the anime thumbnail.
        /// </summary>
        public string GetThumbnail(int episode) {
            return string.Format(THUMBNAIL_URL, episodes[episode].thumbnail);
        }

        public static string GetAnimeType(int type) {

            switch (type) {
                case 0:
                    return "TV";
                case 1:
                    return "OVA";
                case 2:
                    return "Movie";
                case 3:
                    return "Special";
                case 4:
                    return "ONA";
                default:
                    return "TV";
            }
        }

        public int GetEpisodeCount() {
            return episodes.Count();
        }

        public Info info { get; set; }
        public List<Synonyms> synonyms { get; set; }
        public List<Genre> genres { get; set; }
        public string poster { get; set; }
        public int franchise_count { get; set; }
        public List<Wallpaper> wallpapers { get; set; }
        public List<EpisodeData> episodes { get; set; }

        public class Info {

            public int id;
            public string title;
            public string slug;
            public string synopsis;
            public int status;
            public int type;
            public double score;
            public int users_watching;
            public int users_completed;
            public int users_on_hold;
            public int users_planned;
            public int users_dropped;
            public int episode_count;
            public string started_airing_date;
            public string finished_airing_date;
            public string youtube_trailer_id;
            public string age_rating;
            public int episode_length;
            public int tvdb_id;
            public int tvdb_season_id;
            public int tvdb_episode;
            public string wallpaper_id;
            public int wallpaper_offset;
            public int franchise_count;

        }

        public class EpisodeData {
            public EpisodeInfo info;
            public string thumbnail;
        }

        public class EpisodeInfo {

            public int id;
            public int anime_id;
            public string episode;
            public string title;
            public int tvdb_id;
            public string aired;
            public int type;
            public int duration;
            public string description;

        }

        public class Synonyms {

            public string title;
            public int type;

        }

        public class Genre {

            public int id;
            public string name;

        }

        public class Wallpaper {

            public string id;
            public string file;

        }

    }
}
