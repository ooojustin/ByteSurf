﻿using BrotliSharpLib;
using Newtonsoft.Json;
using System;
using System.Collections.Specialized;
using System.IO;
using System.Net;
using System.Text;
using System.Threading;

namespace JexFlix_Scraper.Flixify {

    public static class Flixify {

        private static CookieAwareWebClient Web = new CookieAwareWebClient();
        private const string FLIXIFY = "https://flixify.com/";
        private const string MOVIES_URL = FLIXIFY + "movies?_t=limjml&_u=ji9joxc5ip&add_mroot=1&description=1&g={0}&o=t&p={1}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=movies";
        private const string MOVIES_URL_FOR_DOWNLOAD = FLIXIFY + "{0}?_t=lmispq&_u=ji9joxc5ip&add_mroot=1&cast=0&crew=0&description=1&episodes_list=1&has_sequel=1&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=1&slug=1&sub=1";

        public static void Run() {

            // bypass cloudflare so we can login to and access the website
            Networking.BypassCloudFlare(FLIXIFY + "/login");

            // initialize request headers
            Web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            Web.Headers.Add("Accept-Encoding", "gzip, deflate, br");
            Web.Headers.Add("Accept-Language", "en-US,en;q=0.9,ja;q=0.8");

            // establish post data
            NameValueCollection values = new NameValueCollection();
            values["ref"] = "";
            values["email"] = "nex@weebware.net";
            values["password"] = "fuckniggers69";

            // send request to store cookies from valid login
            Web.UploadValues(FLIXIFY + "/login", values);

            InitializeScraper();

        }

        public static void InitializeScraper() {

            foreach (string genre in genres) {

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
                    byte[] decompressed = Brotli.DecompressBuffer(response, 0, response.Length);
                    string raw = Encoding.Default.GetString(decompressed);

                    // parse video list
                    ParseMovies(raw);

                    Thread.Sleep(1000);

                }

            }

        }

        public const string UPLOAD_URL = "https://cdn.jexflix.com";
        public static void ParseMovies(string raw) {

            MovieData serverData = JsonConvert.DeserializeObject<MovieData>(raw);

            foreach (Movie movie in serverData.items) {

                // if it already exists on the server, go to next movie
                if (Networking.FileExists(movie.title))
                    continue;

                Web.FlixifyHeaders();

                byte[] response = Web.DownloadData(string.Format(MOVIES_URL_FOR_DOWNLOAD, movie.url));
                byte[] response_decompressed = Brotli.DecompressBuffer(response, 0, response.Length);
                string new_raw = Encoding.Default.GetString(response_decompressed);

                RootObject rootObject = JsonConvert.DeserializeObject<RootObject>(new_raw);

                Data data = new Data();
                data.title = rootObject.item.title;
                data.url = rootObject.item.url.Substring(8);
                data.description = rootObject.item.description;
                data.duration = rootObject.item.duration;
                data.thumbnail = UPLOAD_URL + rootObject.item.url + "/thumbnail.jpg";
                data.preview = UPLOAD_URL + rootObject.item.url + "/preview.jpg";
                data.genres = rootObject.item.genres;
                data.imdb_id = rootObject.item.imdb_id;
                data.year = rootObject.item.year;
                data.certification = rootObject.item.certification;

                // setup qualities
                if (rootObject.item.download.download_720 != null) data.qualities.Add(new Qualities { resolution = 720, link = UPLOAD_URL + rootObject.item.url + "/720.mp4" });
                if (rootObject.item.download.download_1080 != null) data.qualities.Add(new Qualities { resolution = 1080, link = UPLOAD_URL + rootObject.item.url + "/1080.mp4" });

                // upload info to insert into database
                Web.UploadString("https://scraper.jexflix.com/add_movie.php", JsonConvert.SerializeObject(data));

                DownloadFiles(rootObject);
                //Networking.UploadFiles(rootObject);
                Directory.Delete(rootObject.item.title.Sanitized(), true);

                Console.WriteLine("Successfully uploaded all data for: " + data.title + Environment.NewLine);

            }

        }

        public const string BASE_IMAGES_URL = "https://a.flixify.com";
        public const string BASE_URL = "https://flixify.com";
        public static void DownloadFiles(RootObject data) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            // create directory to download the files to, we will delete this later.
            string directory = data.item.title.Sanitized();
            if (!Directory.Exists(directory)) Directory.CreateDirectory(directory);

            string preview_url = BASE_IMAGES_URL + data.item.images.preview_large;
            string thumbnail_url = BASE_IMAGES_URL + data.item.images.poster;

            Console.WriteLine("Downloading " + data.item.title + " preview...");
            if (!File.Exists(directory + "/preview.jpg")) web.DownloadFile(preview_url, directory + "/preview.jpg");
            Console.WriteLine("Completed.");
            Console.WriteLine("Downloading " + data.item.title + " thumbnail...");
            if (!File.Exists(directory + "/thumbnail.jpg")) web.DownloadFile(thumbnail_url, directory + "/thumbnail.jpg");
            Console.WriteLine("Completed.");

            if (data.item.download.download_720 != null) {
                Console.WriteLine("Downloading " + data.item.title + " in 720p...");
                if (!File.Exists(directory + "/720.mp4")) web.DownloadFile(BASE_URL + data.item.download.download_720, directory + "/720.mp4");
                Console.WriteLine("Completed.");
            }
            if (data.item.download.download_1080 != null) {
                Console.WriteLine("Downloading " + data.item.title + " in 1080p...");
                if (!File.Exists(directory + "/1080.mp4")) web.DownloadFile(BASE_URL + data.item.download.download_1080, directory + "/1080.mp4");
                Console.WriteLine("Completed.");
            }
        }

        public static void FlixifyHeaders(this CookieAwareWebClient web) {
            web.Headers.Clear(); // clear any existing headers
            web.Headers.Add("Host", "flixify.com");
            web.Headers.Add("Accept", "application/json");
            web.Headers.Add("User-Agent", Networking.USER_AGENT);
            web.Headers.Add("Referer", "https://flixify.com/movies?_rsrc=chrome/newtab");
        }

        private static string[] genres = {
            "animation",
            "documentary",
            "fantasy",
            "music",
            "science-fiction",
            "western",
            "action",
            "comedy",
            "drama",
            "history",
            "mystery",
            "thriller",
            "adventure",
            "crime",
            "family",
            "horror",
            "romance",
            "war"
            };

    }

}
