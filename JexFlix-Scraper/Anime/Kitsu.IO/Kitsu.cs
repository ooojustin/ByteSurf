using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Kitsu.IO {

    public class KitsuAPI {
        // This is the ultimate anime finder and will return the first instance of TV information.
        // You will be able to pass anime names / slug and will be prompted with a load of information using Kitsu.io databases.
        private const string GET_KEYS = "https://kitsu.io/api/edge/algolia-keys";

        // {"params":"query=naruto&attributesToRetrieve=%5B%22id%22%2C%22slug%22%2C%22kind%22%2C%22canonicalTitle%22%2C%22titles%22%2C%22posterImage%22%2C%22subtype%22%2C%22posterImage%22%5D&hitsPerPage=4"}
        private const string MEDIA_API = "https://awqo5j657s-dsn.algolia.net/1/indexes/production_media/query?x-algolia-agent=Algolia%20for%20vanilla%20JavaScript%20(lite)%203.27.1&x-algolia-application-id=AWQO5J657S&x-algolia-api-key={0}";
        private const string MEDIA_API_PARAMS = "query={0}&attributesToRetrieve=%5B%22id%22%2C%22slug%22%2C%22kind%22%2C%22canonicalTitle%22%2C%22titles%22%2C%22posterImage%22%2C%22subtype%22%2C%22posterImage%22%5D&hitsPerPage=4";

        public static Aligolia_Keys GetAligoliaKeys() {
            try {
                string raw = General.GET(GET_KEYS);
                return JsonConvert.DeserializeObject<Aligolia_Keys>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[GetAligoliaKeys] " + ex.Message);
            }
            return null;
        }

        public static Media_Production GetMediaProduction(Aligolia_Keys keys, string query) {
            try {
                MediaPost to_post = new MediaPost();
                to_post.@params = string.Format(MEDIA_API_PARAMS, query);
                string to_upload = JsonConvert.SerializeObject(to_post);
                string raw = General.POST(string.Format(MEDIA_API, keys.media.key), to_upload, "https://kitsu.io");
                return JsonConvert.DeserializeObject<Media_Production>(raw, General.DeserializeSettings);
            } catch (WebException ex) {
                Console.WriteLine("[GetAligoliaKeys] " + ex.Message);
            }
            return null;
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

    }
}
