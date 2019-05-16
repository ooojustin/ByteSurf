using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Kitsu.IO {

    public class KitsuAPI {
        // This is the ultimate anime finder and will return the first instance of TV information.
        // You will be able to pass anime names / slug and will be prompted with a load of information using Kitsu.io databases.
        private const string GET_KEYS = "https://kitsu.io/api/edge/algolia-keys";

        // {"params":"query=naruto&attributesToRetrieve=%5B%22id%22%2C%22slug%22%2C%22kind%22%2C%22canonicalTitle%22%2C%22titles%22%2C%22posterImage%22%2C%22subtype%22%2C%22posterImage%22%5D&hitsPerPage=4"}
        private const string MEDIA_API = "https://awqo5j657s-dsn.algolia.net/1/indexes/production_media/query?x-algolia-agent=Algolia%20for%20vanilla%20JavaScript%20(lite)%203.27.1&x-algolia-application-id=AWQO5J657S&x-algolia-api-key={0}";
        private const string MEDIA_API_PARAMS = "query={0}&attributesToRetrieve=%5B%22id%22%2C%22slug%22%2C%22kind%22%2C%22canonicalTitle%22%2C%22titles%22%2C%22posterImage%22%2C%22subtype%22%2C%22posterImage%22%5D&hitsPerPage=4";
        private const string KITSU_ANIME_LINK = "https://kitsu.io/api/edge/anime/{0}";

        /// <summary>
        /// Generates a key to perform search querys with
        /// </summary>
        public static Aligolia_Keys GetAligoliaKeys() {
            try {
                string raw = General.GET(GET_KEYS);
                return JsonConvert.DeserializeObject<Aligolia_Keys>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[GetAligoliaKeys] " + ex.Message);
            }
            return null;
        }

        /// <summary>
        /// Generate a search query for the different types of medias
        /// </summary>
        public static Media_Production GetMediaProduction(Aligolia_Keys keys, string query) {
            try {
                MediaPost to_post = new MediaPost();
                to_post.@params = string.Format(MEDIA_API_PARAMS, query);
                string to_upload = JsonConvert.SerializeObject(to_post);
                string raw = General.POST(string.Format(MEDIA_API, keys.media.key), to_upload, "https://kitsu.io");
                return JsonConvert.DeserializeObject<Media_Production>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[GetMediaProduction] " + ex.Message);
            }
            return null;
        }

        /// <summary>
        /// Finds the first TV show or the first hit.
        /// </summary>
        public static int GetFirstTVID(Media_Production Medias) {
            if (Medias.hits != null) {
                foreach (Hit hit in Medias.hits) {
                    if (hit.subtype.ToLower().Contains("tv")) {
                        return hit.id;
                    }
                }
            }
            return 0;
        }

        /// <summary>
        /// Automates the search for u given a query
        /// https://kitsu.io/api/edge/anime/11 this is an example of the json data it returns
        /// </summary>
        public static KitsuAnime.Anime GetKitsuAnime(Aligolia_Keys keys, string title) {
            try {
                Media_Production Media = KitsuAPI.GetMediaProduction(keys, title);
                if (Media == null)
                    return null;
                int id = KitsuAPI.GetFirstTVID(Media);
                if (id == 0)
                    return null;
                string raw = General.GET(string.Format(KITSU_ANIME_LINK, id.ToString()));
                if (string.IsNullOrEmpty(raw) || raw.Contains("404"))
                    return null;
                return JsonConvert.DeserializeObject<KitsuAnime.Anime>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[GetKitsuAnime] " + ex.Message);
            }
            return null;
        }

        public static string GetPoster(KitsuAnime.Anime anime) {
            string poster = anime.data.attributes.posterImage.original;
            return poster == null ? "" : poster;
        }

        public static string GetCover(KitsuAnime.Anime anime) {
            string original_cover = anime.data.attributes.coverImage.original;
            return original_cover == null ? "" : original_cover;
        }

        public static string GetRating(KitsuAnime.Anime anime) {
            string rating = anime.data.attributes.averageRating;
            return rating == null ? "50" : rating;
        }

        public static string GetSlug(KitsuAnime.Anime anime) {
            return anime.data.attributes.slug;
        }

        public static List<string> GetSynonyms(KitsuAnime.Anime anime) {
            List<string> synList = new List<string>();
            var attributes = anime.data.attributes;
            if (!string.IsNullOrEmpty(attributes.titles.en))
                synList.Add(attributes.titles.en);
            if (!string.IsNullOrEmpty(attributes.titles.en_jp))
                synList.Add(attributes.titles.en_jp);
            if (!string.IsNullOrEmpty(attributes.canonicalTitle))
                synList.Add(attributes.canonicalTitle);
            if (attributes.abbreviatedTitles != null) {
                foreach (string title in attributes.abbreviatedTitles) {
                    if (!string.IsNullOrEmpty(title))
                        synList.Add(title);
                }
            }
            return synList;
        }

        public static string GetSynopsis(KitsuAnime.Anime anime) {
            string syns = anime.data.attributes.synopsis;
            return syns == null ? "" : syns;
        }

        public static string GetTitle(KitsuAnime.Anime anime) {
            string jp_title = anime.data.attributes.titles.en_jp;
            return jp_title == null ? "" : jp_title;
        }

        private static KitsuAnime.GenreData.Genres FetchGenrePage(KitsuAnime.Anime anime) {
            try {
                string raw = General.GET(anime.data.relationships.genres.links.related);
                return JsonConvert.DeserializeObject<KitsuAnime.GenreData.Genres>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[FetchGenrePage] " + ex.Message);
            }
            return null;
        }

        private static KitsuAnime.EpisodeData.Episodes FetchEpisodePage(KitsuAnime.Anime anime) {
            try {
                string raw = General.GET(anime.data.relationships.episodes.links.related);
                return JsonConvert.DeserializeObject<KitsuAnime.EpisodeData.Episodes>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[FetchEpisodePage] " + ex.Message);
            }
            return null;
        }

        private static KitsuAnime.EpisodeData.Episodes FetchNextEpisodePage(KitsuAnime.EpisodeData.Episodes eps) {
            Thread.Sleep(1000); // so we don't get limited
            try {
                if (!string.IsNullOrEmpty(eps.links.next)) {
                    string raw = General.GET(eps.links.next);
                    var next_page = JsonConvert.DeserializeObject<KitsuAnime.EpisodeData.Episodes>(raw, General.DeserializeSettings);
                    return next_page;
                }
            } catch (WebException ex) {
                Console.WriteLine("[FetchNextEpisodePage] " + ex.Message);
            }
            return null;
        }

        public static List<string> GetGenres(KitsuAnime.Anime anime) {
            List<string> synList = new List<string>();
            var GenreInfo = FetchGenrePage(anime);
            if (GenreInfo == null)
                return null;
            foreach (var genre in GenreInfo.data) {
                if (!string.IsNullOrEmpty(genre.attributes.name))
                    synList.Add(genre.attributes.name);
            }
            return synList;
        }

        public static string GetEpisodeAir(KitsuAnime.Anime anime, int episode) {
            var epdata = FetchEpisodePage(anime);
            if ((epdata.data.Count() - 1) < 0)
                return "";
            // Fetch the last episode
            var last_ep = epdata.data[epdata.data.Count() - 1];
            // When episode is on the next page
            while (episode > last_ep.attributes.number) {
                if (string.IsNullOrEmpty(epdata.links.next))
                    return "";
                var nextep = FetchNextEpisodePage(epdata);
                last_ep = nextep.data[nextep.data.Count() - 1];
                epdata = nextep;
            }
            try {
                foreach (var ep in epdata.data) {
                    if (ep.attributes.number == episode)
                        return ep.attributes.airdate;
                }
            } catch (Exception ex) {
                Console.WriteLine("[GetEpisodeAir] " + ex.Message);
            }
            return "";
        }


        public static string GetEpisodeTitle(KitsuAnime.Anime anime, int episode) {
            var epdata = FetchEpisodePage(anime);
            // Fetch the last episode
            if ((epdata.data.Count() - 1) < 0)
                return "";

            var last_ep = epdata.data[epdata.data.Count() - 1];
            // When episode is on the next page
            while (episode > last_ep.attributes.number) {
                if (string.IsNullOrEmpty(epdata.links.next))
                    return "";
                var nextep = FetchNextEpisodePage(epdata);
                last_ep = nextep.data[nextep.data.Count() - 1];
                epdata = nextep;
            }
            try {
                foreach (var ep in epdata.data) {
                    if (ep.attributes.number == episode)
                        return ep.attributes.titles.en_us;
                }
            } catch (Exception ex) {
                Console.WriteLine("[GetEpisodeTitle] " + ex.Message);
            }
            return "";
        }

        public static string GetAirDate(KitsuAnime.Anime anime) {
            string air_date = anime.data.attributes.startDate;
            return air_date == null ? "" : air_date;
        }

        public static string EpisodeDuration(KitsuAnime.Anime anime) {
            string episode_duration = anime.data.attributes.episodeLength.ToString();
            return episode_duration == null ? "" : episode_duration;
        }

        public static string GetAgeClass(KitsuAnime.Anime anime) {
            string age_rating = anime.data.attributes.ageRating;
            return age_rating == null ? "" : age_rating;
        }

        public class MediaPost {
            public string @params { get; set; }
        }

        public class Users {
            public string key { get; set; }
            public string index { get; set; }
        }

        public class Posts {
            public string key { get; set; }
            public string index { get; set; }
        }

        public class Media {
            public string key { get; set; }
            public string index { get; set; }
        }

        public class Groups {
            public string key { get; set; }
            public string index { get; set; }
        }

        public class Characters {
            public string key { get; set; }
            public string index { get; set; }
        }

        public class Aligolia_Keys {
            public Users users { get; set; }
            public Posts posts { get; set; }
            public Media media { get; set; }
            public Groups groups { get; set; }
            public Characters characters { get; set; }
        }

        public class Titles {
            public string en { get; set; }
            public string en_jp { get; set; }
            public string ja_jp { get; set; }
            public string en_us { get; set; }
        }

        public class Tiny {
            public object width { get; set; }
            public object height { get; set; }
        }

        public class Small {
            public object width { get; set; }
            public object height { get; set; }
        }

        public class Medium {
            public object width { get; set; }
            public object height { get; set; }
        }

        public class Large {
            public object width { get; set; }
            public object height { get; set; }
        }

        public class Dimensions {
            public Tiny tiny { get; set; }
            public Small small { get; set; }
            public Medium medium { get; set; }
            public Large large { get; set; }
        }

        public class Meta {
            public Dimensions dimensions { get; set; }
        }

        public class PosterImage {
            public string tiny { get; set; }
            public string small { get; set; }
            public string medium { get; set; }
            public string large { get; set; }
            public string original { get; set; }
            public Meta meta { get; set; }
        }

        public class En {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public bool fullyHighlighted { get; set; }
            public List<string> matchedWords { get; set; }
        }

        public class EnJp {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public bool fullyHighlighted { get; set; }
            public List<string> matchedWords { get; set; }
        }

        public class JaJp {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public List<object> matchedWords { get; set; }
            public bool? fullyHighlighted { get; set; }
        }

        public class EnUs {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public bool fullyHighlighted { get; set; }
            public List<string> matchedWords { get; set; }
        }

        public class Titles2 {
            public En en { get; set; }
            public EnJp en_jp { get; set; }
            public JaJp ja_jp { get; set; }
            public EnUs en_us { get; set; }
        }

        public class AbbreviatedTitle {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public bool fullyHighlighted { get; set; }
            public List<string> matchedWords { get; set; }
        }

        public class Subtype {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public List<object> matchedWords { get; set; }
        }

        public class Kind {
            public string value { get; set; }
            public string matchLevel { get; set; }
            public List<object> matchedWords { get; set; }
        }

        public class HighlightResult {
            public Titles2 titles { get; set; }
            public List<AbbreviatedTitle> abbreviatedTitles { get; set; }
            public Subtype subtype { get; set; }
            public Kind kind { get; set; }
        }

        public class Hit {
            public Titles titles { get; set; }
            public string canonicalTitle { get; set; }
            public string subtype { get; set; }
            public string slug { get; set; }
            public PosterImage posterImage { get; set; }
            public string kind { get; set; }
            public int id { get; set; }
            public string objectID { get; set; }
            public HighlightResult _highlightResult { get; set; }
        }

        public class Media_Production {
            public List<Hit> hits { get; set; }
            public int nbHits { get; set; }
            public int page { get; set; }
            public int nbPages { get; set; }
            public int hitsPerPage { get; set; }
            public int processingTimeMS { get; set; }
            public bool exhaustiveNbHits { get; set; }
            public string query { get; set; }
            public string queryAfterRemoval { get; set; }
            public string @params { get; set; }
        }

        public class KitsuAnime {

            public class Links {
                public string self { get; set; }
            }

            public class Titles {
                public string en { get; set; }
                public string en_jp { get; set; }
                public string ja_jp { get; set; }
            }

            public class RatingFrequencies {
                public string __invalid_name__2 { get; set; }
                public string __invalid_name__3 { get; set; }
                public string __invalid_name__4 { get; set; }
                public string __invalid_name__5 { get; set; }
                public string __invalid_name__6 { get; set; }
                public string __invalid_name__7 { get; set; }
                public string __invalid_name__8 { get; set; }
                public string __invalid_name__9 { get; set; }
                public string __invalid_name__10 { get; set; }
                public string __invalid_name__11 { get; set; }
                public string __invalid_name__12 { get; set; }
                public string __invalid_name__13 { get; set; }
                public string __invalid_name__14 { get; set; }
                public string __invalid_name__15 { get; set; }
                public string __invalid_name__16 { get; set; }
                public string __invalid_name__17 { get; set; }
                public string __invalid_name__18 { get; set; }
                public string __invalid_name__19 { get; set; }
                public string __invalid_name__20 { get; set; }
            }

            public class Tiny {
                public object width { get; set; }
                public object height { get; set; }
            }

            public class Small {
                public object width { get; set; }
                public object height { get; set; }
            }

            public class Medium {
                public object width { get; set; }
                public object height { get; set; }
            }

            public class Large {
                public object width { get; set; }
                public object height { get; set; }
            }

            public class Dimensions {
                public Tiny tiny { get; set; }
                public Small small { get; set; }
                public Medium medium { get; set; }
                public Large large { get; set; }
            }

            public class Meta {
                public Dimensions dimensions { get; set; }
            }

            public class PosterImage {
                public string tiny { get; set; }
                public string small { get; set; }
                public string medium { get; set; }
                public string large { get; set; }
                public string original { get; set; }
                public Meta meta { get; set; }
            }

            public class Tiny2 {
                public int width { get; set; }
                public int height { get; set; }
            }

            public class Small2 {
                public int width { get; set; }
                public int height { get; set; }
            }

            public class Large2 {
                public int width { get; set; }
                public int height { get; set; }
            }

            public class Dimensions2 {
                public Tiny2 tiny { get; set; }
                public Small2 small { get; set; }
                public Large2 large { get; set; }
            }

            public class Meta2 {
                public Dimensions2 dimensions { get; set; }
            }

            public class CoverImage {
                public string tiny { get; set; }
                public string small { get; set; }
                public string large { get; set; }
                public string original { get; set; }
                public Meta2 meta { get; set; }
            }

            public class Attributes {
                public DateTime createdAt { get; set; }
                public DateTime updatedAt { get; set; }
                public string slug { get; set; }
                public string synopsis { get; set; }
                public int coverImageTopOffset { get; set; }
                public Titles titles { get; set; }
                public string canonicalTitle { get; set; }
                public List<string> abbreviatedTitles { get; set; }
                public string averageRating { get; set; }
                public RatingFrequencies ratingFrequencies { get; set; }
                public int userCount { get; set; }
                public int favoritesCount { get; set; }
                public string startDate { get; set; }
                public string endDate { get; set; }
                public object nextRelease { get; set; }
                public int popularityRank { get; set; }
                public int ratingRank { get; set; }
                public string ageRating { get; set; }
                public string ageRatingGuide { get; set; }
                public string subtype { get; set; }
                public string status { get; set; }
                public string tba { get; set; }
                public PosterImage posterImage { get; set; }
                public CoverImage coverImage { get; set; }
                public int episodeCount { get; set; }
                public int episodeLength { get; set; }
                public int totalLength { get; set; }
                public string youtubeVideoId { get; set; }
                public string showType { get; set; }
                public bool nsfw { get; set; }
            }

            public class Links2 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Genres {
                public Links2 links { get; set; }
            }

            public class Links3 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Categories {
                public Links3 links { get; set; }
            }

            public class Links4 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Castings {
                public Links4 links { get; set; }
            }

            public class Links5 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Installments {
                public Links5 links { get; set; }
            }

            public class Links6 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Mappings {
                public Links6 links { get; set; }
            }

            public class Links7 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Reviews {
                public Links7 links { get; set; }
            }

            public class Links8 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class MediaRelationships {
                public Links8 links { get; set; }
            }

            public class Links9 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Characters {
                public Links9 links { get; set; }
            }

            public class Links10 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Staff {
                public Links10 links { get; set; }
            }

            public class Links11 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Productions {
                public Links11 links { get; set; }
            }

            public class Links12 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Quotes {
                public Links12 links { get; set; }
            }

            public class Links13 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class Episodes {
                public Links13 links { get; set; }
            }

            public class Links14 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class StreamingLinks {
                public Links14 links { get; set; }
            }

            public class Links15 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class AnimeProductions {
                public Links15 links { get; set; }
            }

            public class Links16 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class AnimeCharacters {
                public Links16 links { get; set; }
            }

            public class Links17 {
                public string self { get; set; }
                public string related { get; set; }
            }

            public class AnimeStaff {
                public Links17 links { get; set; }
            }

            public class Relationships {
                public Genres genres { get; set; }
                public Categories categories { get; set; }
                public Castings castings { get; set; }
                public Installments installments { get; set; }
                public Mappings mappings { get; set; }
                public Reviews reviews { get; set; }
                public MediaRelationships mediaRelationships { get; set; }
                public Characters characters { get; set; }
                public Staff staff { get; set; }
                public Productions productions { get; set; }
                public Quotes quotes { get; set; }
                public Episodes episodes { get; set; }
                public StreamingLinks streamingLinks { get; set; }
                public AnimeProductions animeProductions { get; set; }
                public AnimeCharacters animeCharacters { get; set; }
                public AnimeStaff animeStaff { get; set; }
            }

            public class Data {
                public string id { get; set; }
                public string type { get; set; }
                public Links links { get; set; }
                public Attributes attributes { get; set; }
                public Relationships relationships { get; set; }
            }

            public class Anime {
                public Data data { get; set; }
            }

            public class EpisodeData {
                public class Links {
                    public string self { get; set; }
                }

                public class Titles {
                    public string en_jp { get; set; }
                    public string en_us { get; set; }
                    public string ja_jp { get; set; }
                }

                public class Dimensions {
                }

                public class Meta {
                    public Dimensions dimensions { get; set; }
                }

                public class Thumbnail {
                    public string original { get; set; }
                    public Meta meta { get; set; }
                }

                public class Attributes {
                    public DateTime createdAt { get; set; }
                    public DateTime updatedAt { get; set; }
                    public Titles titles { get; set; }
                    public string canonicalTitle { get; set; }
                    public int seasonNumber { get; set; }
                    public int number { get; set; }
                    public int relativeNumber { get; set; }
                    public string synopsis { get; set; }
                    public string airdate { get; set; }
                    public int length { get; set; }
                    public Thumbnail thumbnail { get; set; }
                }

                public class Links2 {
                    public string self { get; set; }
                    public string related { get; set; }
                }

                public class Media {
                    public Links2 links { get; set; }
                }

                public class Links3 {
                    public string self { get; set; }
                    public string related { get; set; }
                }

                public class Videos {
                    public Links3 links { get; set; }
                }

                public class Relationships {
                    public Media media { get; set; }
                    public Videos videos { get; set; }
                }

                public class Datum {
                    public string id { get; set; }
                    public string type { get; set; }
                    public Links links { get; set; }
                    public Attributes attributes { get; set; }
                    public Relationships relationships { get; set; }
                }

                public class Meta2 {
                    public int count { get; set; }
                }

                public class Links4 {
                    public string first { get; set; }
                    public string next { get; set; }
                    public string last { get; set; }
                }

                public class Episodes {
                    public List<Datum> data { get; set; }
                    public Meta2 meta { get; set; }
                    public Links4 links { get; set; }
                }
            }

            public class GenreData {

                public class Links {
                    public string self { get; set; }
                }

                public class Attributes {
                    public DateTime createdAt { get; set; }
                    public DateTime updatedAt { get; set; }
                    public string name { get; set; }
                    public string slug { get; set; }
                    public string description { get; set; }
                }

                public class Datum {
                    public string id { get; set; }
                    public string type { get; set; }
                    public Links links { get; set; }
                    public Attributes attributes { get; set; }
                }

                public class Meta {
                    public int count { get; set; }
                }

                public class Links2 {
                    public string first { get; set; }
                    public string last { get; set; }
                }

                public class Genres {
                    public List<Datum> data { get; set; }
                    public Meta meta { get; set; }
                    public Links2 links { get; set; }
                }
            }
        }
    }
}
