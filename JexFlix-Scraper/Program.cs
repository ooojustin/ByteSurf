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

class Program {
    // uwu
    public static CookieAwareWebClient web = new CookieAwareWebClient();
    public static string BASE_URL = "https://flixify.com/";
    public static string MOVIES_URL = "https://flixify.com/movies?_t=limjml&_u=ji9joxc5ip&add_mroot=1&description=1&g={0}&o=t&p={1}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=movies";

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
                SetHeaders();
                string URL = string.Format(MOVIES_URL, genre, page);

                // check if the page returns a 404 to move onto the next genre
                try {
                    response = web.DownloadData(URL);
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

    public static void Parse(string raw) {
        ServerData data = JsonConvert.DeserializeObject<ServerData>(raw);

        foreach (Item x in data.items) {
            Console.WriteLine(x.title);
        }
    }

    public static void SetHeaders() {
        web.Headers.Add("Host", "flixify.com");
        web.Headers.Add("Accept", "application/json");
        web.Headers.Add("User-Agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
        web.Headers.Add("Referer", "https://flixify.com/movies?_rsrc=chrome/newtab");
    }
}