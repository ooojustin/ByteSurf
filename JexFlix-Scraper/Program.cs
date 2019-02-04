using BrotliSharpLib;
using CloudFlareUtilities;
using JexFlix_Scraper;
using System;
using System.Collections.Specialized;
using System.Net;
using System.Net.Http;
using System.Text;
using Newtonsoft.Json;
using System.Threading;
using System.IO;
using System.Collections.Generic;
using JexFlix_Scraper.Classes;

class Program {
    // uwu
    public static CookieAwareWebClient web = new CookieAwareWebClient();
    public const string BASE_URL = "https://flixify.com/";
    public const string MOVIES_URL = "https://flixify.com/movies?_t=limjml&_u=ji9joxc5ip&add_mroot=1&description=1&g={0}&o=t&p={1}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=movies";
    public const string MOVIES_URL_FOR_DOWNLOAD = "https://flixify.com/{0}?_t=lmispq&_u=ji9joxc5ip&add_mroot=1&cast=0&crew=0&description=1&episodes_list=1&has_sequel=1&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=1&slug=1&sub=1";


    static void Main(string[] args) {
        ClearanceHandler handler = new ClearanceHandler();
        HttpClient client = new HttpClient(handler);

        // set useragent and some headers, must be these
        client.DefaultRequestHeaders.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        web.Headers.Add("Accept-Encoding", "gzip, deflate, br");
        web.Headers.Add("Accept-Language", "en-US,en;q=0.9,ja;q=0.8");

        // any kind of request using our cloudflare bypass will set cookies for later use in the webclient
        string content = client.GetStringAsync(BASE_URL + "/login").Result;

        NameValueCollection values = new NameValueCollection();
        values["ref"] = "";
        values["email"] = "nex@weebware.net";
        values["password"] = "fuckniggers69";

        web.UploadValues(BASE_URL + "/login", values);

        InitializeScraper();
        Console.ReadKey();

    }

    public static string[] genres = {
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

    public static byte[] response;
    public static void InitializeScraper() {
        foreach (string genre in genres) {
            for (int page = 1; page <= 100; page++) {
                Networking.SetHeaders(web);
                string url = string.Format(MOVIES_URL, genre, page);

                // check if the page returns a 404 to move onto the next genre
                try {
                    response = web.DownloadData(url);
                } catch (WebException ex) {
                    HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                    if (webResponse.StatusCode == HttpStatusCode.NotFound) break;
                }

                byte[] response_decompressed = Brotli.DecompressBuffer(response, 0, response.Length);
                string raw_data = Encoding.Default.GetString(response_decompressed);

                Parse(raw_data);
                Thread.Sleep(1000);
            }
        }
    }

    public const string UPLOAD_URL = "https://cdn.jexflix.com";
    public static void Parse(string raw) {
        ServerData serverData = JsonConvert.DeserializeObject<ServerData>(raw);

        foreach (Item x in serverData.items) {
            SafeRequest.Response safeResponse = Networking.CheckFileExists(x.title);

            // if it already exists on the server, go to next movie
            if (safeResponse.GetData<bool>("exists")) continue;

            Networking.SetHeaders(web);

            byte[] response = web.DownloadData(string.Format(MOVIES_URL_FOR_DOWNLOAD, x.url));
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
            if (rootObject.item.download.download_720 != null) data.qualities.Add(new Qualities {resolution = 720, link = UPLOAD_URL + rootObject.item.url + "/720.mp4"});
            if (rootObject.item.download.download_1080 != null)  data.qualities.Add(new Qualities {resolution = 1080, link = UPLOAD_URL + rootObject.item.url + "/1080.mp4" });

            // upload info to insert into database
            web.UploadString("https://scraper.jexflix.com/add_movie.php", JsonConvert.SerializeObject(data));

            Networking.DownloadFiles(rootObject);
            Networking.UploadFiles(rootObject);
            Directory.Delete(rootObject.item.title, true);

            Console.WriteLine("Successfully uploaded: " + data.title);

        }
    }
}