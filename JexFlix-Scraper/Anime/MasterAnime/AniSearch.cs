using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.MasterAnime {

    public class AniSearch {

        private const string FILTER_SEARCH_URL = "https://www.masterani.me/api/anime/filter?search={0}&order={1}";
        private const string FILTER_URL = "https://www.masterani.me/api/anime/filter?order={0}&page={1}";
        private const string FILTER_GENRES_URL = "https://www.masterani.me/api/anime/filter?order={0}&genres={1}&page={2}";
        private const string THUMBNAIL_URL = "https://cdn.masterani.me/{0}/{1}/{2}";

        public int total;
        public int per_page;
        public int current_page;
        public int last_page;
        public string next_page_url;
        public string prev_page_url;
        public int from;
        public int to;
        public List<Show> data; // shows

        public class Show {

            public int id;
            public string title;
            public string slug;
            public int status;
            public int type;
            public double score;
            public int episode_count;
            public string started_airing_date;
            public string finished_airing_date;
            public List<Genre> genres;
            public Poster poster;

            /// <summary>
            /// Gets a link to the anime thumbnail.
            /// </summary>
            public string GetThumbnail(int sizeIndex = 1) {
                return string.Format(THUMBNAIL_URL, poster.path.TrimEnd('/'), sizeIndex, poster.file);
            }

            /// <summary>
            /// Returns an anime object for the current show.
            /// </summary>
            public AniInfo GetAnime() {
                return AniInfo.GetAnime(id);
            }

        }

        public class Genre {

            public int id;
            public string name;

        }

        public class Poster {

            public string id;
            public string path;
            public string extension;
            public string file;

        }

        /// <summary>
        /// Gets the Anime provided - Method of sorting, page number, default search, any genre of the anime specified.
        /// </summary>
        public static AniSearch GetAnime(string query = "", int page = 1, int[] genres = null, SortMethod method = SortMethod.HIGH_SCORE, int type = 0, int status = 0, Action<AniSearch> callback = null) {

            string GetURL() {
                // if we have a search query, ignore page # + sorting method + genres
                if (!string.IsNullOrEmpty(query)) {
                    return string.Format(FILTER_SEARCH_URL, Uri.EscapeUriString(query), GetSortMethod(SortMethod.HIGH_RELEVANCE));
                }

                if (genres == null) {

                    return string.Format(FILTER_URL, GetSortMethod(method), page);
                } else {
                    string strGenres = string.Empty;
                    foreach (int genre in genres)
                        strGenres += genre + ",";
                    return string.Format(FILTER_GENRES_URL, GetSortMethod(method), strGenres.TrimEnd(','), page);
                }
            }

            string get_url = GetURL();

            if (type > 0) {
                get_url += "&type=" + (type - 1).ToString();
            }

            if (status > 0) {
                get_url += "&status=" + (status - 1).ToString();
            }

            string data = General.GET(get_url); // General.HttpClient_GETAsync(get_url);

            // Console.WriteLine(data);

            try {
                AniSearch animeFinder = JsonConvert.DeserializeObject<AniSearch>(data, General.DeserializeSettings);
                callback?.Invoke(animeFinder);
                return animeFinder;
            } catch (Exception) {
                callback?.Invoke(null);
                return null;
            }

        }

        /// <summary>
        /// Converts the given enum into the relevant string used for sorting
        /// </summary>
        public static string GetSortMethod(SortMethod method = SortMethod.HIGH_SCORE) {
            switch (method) {
                case SortMethod.HIGH_RELEVANCE: return "relevance_desc";
                case SortMethod.LOW_RELEVANCE: return "relevance";
                case SortMethod.HIGH_SCORE: return "score_desc";
                case SortMethod.LOW_SCORE: return "score";
                case SortMethod.NAME_A_Z: return "title";
                case SortMethod.NAME_Z_A: return "title_desc";
                default: return "unknown";
            }
        }

        public static string GetStatus(int i) {
            return i.ToString();
        }

        public static string GetType(int i) {
            return i.ToString();
        }

        public enum SortMethod {
            HIGH_RELEVANCE,
            LOW_RELEVANCE,
            HIGH_SCORE,
            LOW_SCORE,
            NAME_A_Z,
            NAME_Z_A
        }
    }
}
