using BrotliSharpLib;
using Newtonsoft.Json;
using System;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading;

namespace JexFlix_Scraper.Flixify {

    public static class Flixify {

        private static CookieContainer Cookies = null;

        private const string FLIXIFY = "https://flixify.com/";
        private const string MOVIES_URL = FLIXIFY + "movies?_t=limjml&_u=ji9joxc5ip&add_mroot=1&description=1&g={0}&o=t&p={1}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=movies";
        private const string MOVIES_URL_FOR_DOWNLOAD = FLIXIFY + "{0}?_t=lmispq&_u=ji9joxc5ip&add_mroot=1&cast=0&crew=0&description=1&episodes_list=1&has_sequel=1&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=1&slug=1&sub=1";


        public static void Run(int genre_index) {

            // note to self
            // improve this stuff, lol
            CookieAwareWebClient web = new CookieAwareWebClient();

            // bypass cloudflare so we can login to and access the website
            Networking.BypassCloudFlare(FLIXIFY + "/login", out Cookies);

            // initialize request headers
            web.Cookies = Cookies;
            web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            web.Headers.Add("Accept-Encoding", "gzip, deflate, br");
            web.Headers.Add("Accept-Language", "en-US,en;q=0.9,ja;q=0.8");

            // establish post data
            NameValueCollection values = new NameValueCollection();
            values["ref"] = "";
            values["email"] = "justin@garofolo.net";
            values["password"] = "D3MU&DvWm9%xf*z";

            // send request to store cookies from valid login
            web.UploadValues(FLIXIFY + "/login", values);

            InitializeScraper(web, genre_index);
        }

        public static void InitializeScraper(CookieAwareWebClient Web, int genre_index) {

            foreach (string genre in genres.Skip(genre_index)) {

                for (int page = 1; page <= 100; page++) {

                    // apply headers to web request
                    Web.FlixifyHeaders();
                    byte[] response = null;

                    try {
                        string url = string.Format(MOVIES_URL, genre, page);
                        response = Web.DownloadData(url);
                    } catch (WebException ex) {
                        // catch NotFound exception
                        // continue to next genre (out of videos)
                        HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                        if (webResponse.StatusCode == HttpStatusCode.NotFound)
                            break;
                    }

                    // decompress and establish response from server
                    //byte[] decompressed = Brotli.DecompressBuffer(response, 0, response.Length);
                    string raw = Encoding.Default.GetString(response);

                    // parse video list
                    ParseMovies(raw, Web);

                    Thread.Sleep(1000);

                }

            }

        }

        public static void ParseMovies(string raw, CookieAwareWebClient Web) {

            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            GenreData serverData = JsonConvert.DeserializeObject<GenreData>(raw);

            foreach (Movie movie in serverData.items) {

                // if it already exists on the server, go to next movie
                if (Networking.FileExists(movie.url.Substring(8)))
                    continue;

                // stuff starts here to delete

                //Web.FlixifyHeaders();
                //byte[] response = Web.DownloadData(string.Format(MOVIES_URL_FOR_DOWNLOAD, movie.url));
                //string new_raw = Encoding.Default.GetString(response);
                //MovieData rootObject = JsonConvert.DeserializeObject<MovieData>(new_raw);

                //string thumbnail_url = BASE_IMAGES_URL + rootObject.item.images.poster;
                //string url_on_cdn = Networking.CDN_URL + rootObject.item.url + "/thumbnail.jpg";
                //double? rating = rootObject.item.rating;
                //string directory = rootObject.item.url;

                //using (StreamWriter sw = File.AppendText("purgelist2k19.txt")) {
                //    sw.WriteLine(url_on_cdn);
                //}


                //if (rootObject.item.images.poster != null)
                //    Networking.ReuploadRemoteFile(FixExtension(FixThumbnailRes(thumbnail_url)), directory, "thumbnail.jpg", rootObject.item.title, web);

                //Console.WriteLine(Convert.ToString(rating));

                //NameValueCollection values = new NameValueCollection();
                //values["rating"] = Convert.ToString(rating);
                //values["url"] = rootObject.item.url.Substring(8);

                //web.UploadValues("https://scraper.jexflix.com/add_rating.php", values);

                //Console.WriteLine("Rating added to " + rootObject.item.title);

                // stuff ends here to delete

                //MessageHandler.Add(movie.title, "Beginning reupload process", ConsoleColor.White, ConsoleColor.Yellow);
                Console.WriteLine("[" + movie.title + "] " + "Beginning reupload process");

                Web.FlixifyHeaders();

                byte[] response = Web.DownloadData(string.Format(MOVIES_URL_FOR_DOWNLOAD, movie.url));
                // byte[] response_decompressed = Brotli.DecompressBuffer(response, 0, response.Length);
                string new_raw = Encoding.Default.GetString(response);

                MovieData rootObject = JsonConvert.DeserializeObject<MovieData>(new_raw);

                Data data = new Data();
                data.title = rootObject.item.title;
                data.url = rootObject.item.url.Substring(8);

                // these can all be returned as null at times, so lets check so it doesnt fuck the sql query
                if (rootObject.item.description != null)
                    data.description = rootObject.item.description;

                if (rootObject.item.url != null) {
                    data.preview = Networking.CDN_URL + rootObject.item.url + "/preview.jpg";
                    data.thumbnail = Networking.CDN_URL + rootObject.item.url + "/thumbnail.jpg";
                }

                if (rootObject.item.duration != null)
                    data.duration = rootObject.item.duration;

                if (rootObject.item.rating != null)
                    data.rating = rootObject.item.rating;

                if (rootObject.item.genres != null)
                    data.genres = rootObject.item.genres;

                if (rootObject.item.year != null)
                    data.year = rootObject.item.year;

                if (rootObject.item.imdb_id != null)
                    data.imdb_id = rootObject.item.imdb_id;

                if (rootObject.item.certification != null)
                    data.certification = rootObject.item.certification;

                // setup qualities
                if (rootObject.item.download.download_720 != null)
                    data.qualities.Add(new Quality { resolution = 720, link = Networking.CDN_URL + rootObject.item.url + "/720.mp4" });

                if (rootObject.item.download.download_1080 != null)
                    data.qualities.Add(new Quality { resolution = 1080, link = Networking.CDN_URL + rootObject.item.url + "/1080.mp4" });

                if (rootObject.item.download.download_720 == null && rootObject.item.download.download_1080 == null)
                    data.qualities.Add(new Quality { resolution = 480, link = Networking.CDN_URL + rootObject.item.url + "/480.mp4" });

                // upload info to insert into database

                ReuploadFiles(rootObject);


                while (true) {
                    try { Web.UploadString("https://bytesurf.io/scraper/add_movie.php", JsonConvert.SerializeObject(data)); } catch (WebException ex) {
                        Thread.Sleep(60000);
                        continue;
                    }
                    break;
                }


                //MessageHandler.Add(movie.title, "Completed reupload process", ConsoleColor.White, ConsoleColor.Yellow);
                Console.WriteLine("[" + movie.title + "] " + "Completed reupload process");

            }

        }

        public const string BASE_IMAGES_URL = "https://a.flixify.com";
        public const string BASE_URL = "https://flixify.com";

        public static void ReuploadFiles(MovieData data) {

            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            // create directory to download the files to, we will delete this later.
            string directory = data.item.url;

            string preview_url = BASE_IMAGES_URL + data.item.images.preview_large;
            string thumbnail_url = BASE_IMAGES_URL + data.item.images.poster;

            // make sure we dont try downloading files that dont exist
            if (data.item.images.preview_large != null)
                Networking.ReuploadRemoteFile(FixExtension(preview_url), directory, "preview.jpg", data.item.title, web);
            if (data.item.images.poster != null)
                Networking.ReuploadRemoteFile(FixExtension(FixThumbnailRes(thumbnail_url)), directory, "thumbnail.jpg", data.item.title, web);
            if (data.item.download.download_720 != null)
                Networking.ReuploadRemoteFile(BASE_URL + data.item.download.download_720, directory, "720.mp4", data.item.title, web);
            if (data.item.download.download_1080 != null)
                Networking.ReuploadRemoteFile(BASE_URL + data.item.download.download_1080, directory, "1080.mp4", data.item.title, web);
            if (data.item.download.download_720 == null && data.item.download.download_1080 == null)
                Networking.ReuploadRemoteFile(BASE_URL + data.item.download.download_480, directory, "480.mp4", data.item.title, web);
        }

        /// <summary>
        /// Apply Flixify request headers to a given WebClient.
        /// </summary>
        public static void FlixifyHeaders(this WebClient web) {
            web.Headers.Clear(); // clear any existing headers
            web.Headers.Add("Host", "flixify.com");
            web.Headers.Add("Accept", "application/json");
            web.Headers.Add("User-Agent", Networking.USER_AGENT);
            web.Headers.Add("Referer", "https://flixify.com/movies?_rsrc=chrome/newtab");
        }

        /// <summary>
        /// Changes a string to fix the file extension because flixify is braindead
        /// </summary>
        public static string FixExtension(string parse) {
            if (parse.Contains("jpg")) return parse;
            return parse.Replace("jpeg", "jpg");
        }

        public static string FixThumbnailRes(string url) {
            return url.Replace("172x255", "370x549");
        }

        /// <summary>
        /// List of genres to parse via Flixify categories.
        /// </summary>
        private static string[] genres = {
            "animation", // 0
            "fantasy", // 1
            "science-fiction", // 2
            "music", // 3
            "documentary", // 4
            "western", // 5
            "action", // 6
            "comedy", // 7
            "drama", // 8
            "history", // 9
            "mystery", // 10
            "thriller", // 11
            "adventure", // 12
            "crime", // 13
            "family", // 14
            "horror", // 15
            "romance", // 16
            "war" // 17
            };

    }

}
