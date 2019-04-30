using JexFlix_Scraper.Flixify;
using JexFlix_Scraper.Series;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading.Tasks;
using static JexFlix_Scraper.Series.EpisodeClasses;
using static JexFlix_Scraper.Series.PageClasses;
using static JexFlix_Scraper.Series.SeasonClasses;
using static JexFlix_Scraper.Series.UploadClasses;

namespace JexFlix_Scraper.Shows {
    class Shows {

        private static CookieContainer Cookies = null;

        private const string FLIXIFY = "https://calmx.site/";
        private const string SHOW_URL = FLIXIFY + "/shows?_t=nu7m7a&_u=ji9joxc5ip&add_mroot=1&description=1&o=t&p={0}&postersize=poster&previewsizes=%7B%22preview_list%22:%22big3-index%22,%22preview_grid%22:%22video-block%22%7D&slug=1&type=shows";
        private const string GET_SEASONS = FLIXIFY + "{0}?_t=ijbgom&_u=ji9joxc5ip&add_mroot=1&add_sequels=1&cast=0&crew=0&description=1&episodes_list=0&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=1&slug=1";
        private const string GET_EPISODES = FLIXIFY + "{0}?_t=aml7bt&_u=ji9joxc5ip&add_mroot=1&add_sequels=1&cast=0&crew=0&description=1&episodes_list=1&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=0&slug=1 ";
        private const string GET_EPISODE_SPECIFIC = FLIXIFY + "{0}?_t=jot7um&_u=ld475wbbcn&add_mroot=0&add_sequels=1&cast=0&crew=0&description=1&episodes_list=0&postersize=poster&previews=1&previewsizes=%7B%22preview_grid%22:%22video-block%22,%22preview_list%22:%22big3-index%22%7D&season_list=0&slug=1&sub=1";

        public static void Run() {

            CookieAwareWebClient web = new CookieAwareWebClient();
            web.InitializeHeaders();
            string response = web.DownloadStringBrotli(FLIXIFY + "login");

            // the following cookies should exist: __cfduid, pip, promo_id, session
            Networking.OutputCookies(web.Cookies);

            Cookies = web.Cookies;

            NameValueCollection values = new NameValueCollection();
            values["ref"] = "";
            values["email"] = "justin@garofolo.net";
            values["password"] = "D3MU&DvWm9%xf*z";
            // values["authenticity_token"] = response.GetAuthenticityToken();

            // these 2 probably don't matter, we still don't know what they do
            values["d"] = "57";
            values["t"] = "262";

            web.InitializeHeaders();

            // these make the request seem more natural
            web.Headers.Add("Origin", "https://calmx.site");
            web.Headers.Add("Referer", "https://calmx.site/login");

            web.UploadValues(FLIXIFY + "login", values);

            InitializeScraper(web);
        }

        public static void InitializeScraper(CookieAwareWebClient web) {
            // 100 pages, if theres more than 3000 results idc nobody is watching 90% of these shows anyways.

            for (int page = 1; page <= 100; page++) {
                web.FlixifyHeaders();
                byte[] response = null;

                try {
                    string url = string.Format(SHOW_URL, page);
                    response = web.DownloadData(url);
                } catch (WebException ex) {
                    // catch NotFound exception
                    // continue to next genre (out of videos)
                    HttpWebResponse webResponse = ex.Response as HttpWebResponse;
                    if (webResponse.StatusCode == HttpStatusCode.NotFound)
                        break;
                }
                string raw = Encoding.Default.GetString(response);
                ParseShows(raw);
                Console.ReadKey();
            }

        }

        public static void ParseShows(string raw) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            PageObject pageData = JsonConvert.DeserializeObject<PageObject>(raw);

            foreach (PageItem item in pageData.items) {

                SeriesData series = new SeriesData();
                series.title = item.title;
                series.url = item.url.Substring(7);
                series.dataurl = Networking.CDN_URL + item.url + "/data.json";
                series.preview = Networking.CDN_URL + item.url + "/preview.jpg";
                series.thumbnail = Networking.CDN_URL + item.url + "/thumbnail.jpg";
                series.genres = item.genres;
                series.description = item.description;

                if (item.year != null)
                    series.year = item.year;

                if (item.imdb_id != null)
                    series.imdb_id = item.imdb_id;

                if (item.certification != null)
                    series.certification = item.certification;

                if (item.rating != null)
                    series.rating = item.rating;

                // reupload preview, and thumbnail
                string preview_url = BASE_IMAGES_URL + item.images.preview;
                string thumbnail_url = BASE_IMAGES_URL + item.images.poster;

                if (item.images.preview != null)
                    Networking.ReuploadRemoteFile(FixPosterRes(preview_url), item.url, "preview.jpg", item.title, web);
                if (item.images.poster != null)
                    Networking.ReuploadRemoteFile(FixThumbnailRes(thumbnail_url), item.url, "thumbnail.jpg", item.title, web);

                // add the series, checks if exists server sided
                try {
                    web.UploadString("https://bytesurf.io/scraper/add_series.php", JsonConvert.SerializeObject(series));
                } catch {

                }
                string data_url = Networking.JsonData(series.url);
                string ftp_directory = data_url.Substring(Networking.CDN_URL.Length, data_url.Length - Networking.CDN_URL.Length);

                string raw_data_json = Networking.DownloadStringFTP(ftp_directory);

                ParseSeasons(item.url, raw_data_json, item.url, item.title);

            }
        }

        public static void ParseSeasons(string series, string season_json, string item_url, string title) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            string url = string.Format(GET_SEASONS, series);
            string response = null;

            web.FlixifyHeaders();
            response = web.DownloadString(url);

            string raw = response;
            SeasonObject seasonData = JsonConvert.DeserializeObject<SeasonObject>(raw);

            SeriesList seriesList = new SeriesList();

            int season_number = 1;
            foreach (SeasonItem season in seasonData.seasons) {
                seriesList.seasons.Add(new SeasonData { title = season.title, season = season_number, description = season.description, released = season.released_sec_ago });
                season_number++;
            }
            string path = Path.GetTempFileName();
            File.WriteAllText(path, JsonConvert.SerializeObject(seriesList));
            Networking.UploadFile(path, item_url, "data.json", seasonData.item.title);
            File.Delete(path);

            // reset season number for use again here
            season_number = 1;
            foreach (SeasonItem season in seasonData.seasons) {
                int their_current_count = GetTheirEpisodeData(season.url).episodes.Count;
                int our_current_count = GetOurEpisodeData(series, season_number).episodes.Count;

                while (our_current_count < their_current_count) {
                    ReuploadEpisodes(series, season_number, our_current_count, title, GetTheirEpisodeData(season.url));
                    our_current_count++;
                }
                season_number++;
            }
        }

        public const string BASE_IMAGES_URL = "https://a.calmx.site";
        public const string BASE_URL = "https://calmx.site";

        public static void ReuploadEpisodes(string season, int season_number, int episode, string title, EpisodeObject episodeData) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            web.FlixifyHeaders();

            string specific_episode_url = string.Format(GET_EPISODE_SPECIFIC, episodeData.episodes[episode].url);
            string raw_data = web.DownloadString(specific_episode_url);

            SubtitlesThing subClass = JsonConvert.DeserializeObject<SubtitlesThing>(raw_data);

            string season_directory = season + "/" + season_number;

            EpisodeList series = null;

            if (episode > 0) {
                string ftp_directory = season + "/" + season_number + "/data.json";
                string raw = Networking.DownloadStringFTP(ftp_directory);
                series = JsonConvert.DeserializeObject<EpisodeList>(raw);
            } else series = new EpisodeList();

            TimeSpan t = DateTime.UtcNow - new DateTime(1970, 1, 1);
            int timestamp = (int)t.TotalSeconds;

            Episode newEpisode = new Episode();
            newEpisode.title = episodeData.episodes[episode].title;
            newEpisode.episode = (episode + 1);
            newEpisode.description = episodeData.episodes[episode].description;
            newEpisode.released = (timestamp - episodeData.episodes[episode].released_sec_ago);

            EpisodeInfo episodeInfo = new EpisodeInfo();
            episodeInfo.title = episodeData.episodes[episode].title;
            episodeInfo.episode = (episode + 1);
            episodeInfo.description = episodeData.episodes[episode].description;
            episodeInfo.released = (timestamp - episodeData.episodes[episode].released_sec_ago);


            string directory = season_directory + "/" + (episode + 1);

            if (episodeData.episodes[episode].download.download_720 == null && episodeData.episodes[episode].download.download_1080 == null) {
                episodeInfo.qualities.Add(new Qualities { resolution = 480 });
                Networking.ReuploadRemoteFile(BASE_URL + episodeData.episodes[episode].download.download_480, directory, "480.mp4", title + " - " + newEpisode.title, web);
            }

            if (episodeData.episodes[episode].download.download_720 != null) {
                episodeInfo.qualities.Add(new Qualities { resolution = 720 });
                Networking.ReuploadRemoteFile(BASE_URL + episodeData.episodes[episode].download.download_720, directory, "720.mp4", title + " - " + newEpisode.title, web);
            }

            if (episodeData.episodes[episode].download.download_1080 != null) {
                episodeInfo.qualities.Add(new Qualities { resolution = 1080 });
                Networking.ReuploadRemoteFile(BASE_URL + episodeData.episodes[episode].download.download_1080, directory, "1080.mp4", title + " - " + newEpisode.title, web);
            }

            if (subClass.item.subtitles.eng != null) {
                episodeInfo.subs.Add(new UploadClasses.Subs { language = "en", url = directory + "/en.vtt" });
                Networking.ReuploadRemoteFile(BASE_IMAGES_URL + subClass.item.subtitles.eng[0].url, directory, "en.vtt", title + " - " + newEpisode.title, web);
            }

            series.episodes.Add(newEpisode);

            string path;

            path = Path.GetTempFileName();
            File.WriteAllText(path, JsonConvert.SerializeObject(series));
            Networking.UploadFile(path, season_directory, "data.json", title + " - " + newEpisode.title);
            File.Delete(path);

            path = Path.GetTempFileName();
            File.WriteAllText(path, JsonConvert.SerializeObject(episodeInfo));
            Networking.UploadFile(path, directory, "data.json", title + " - " + episodeInfo.title);
            File.Delete(path);

        }

        public static EpisodeList GetOurEpisodeData(string series, int season) {

            string ftp_directory = series + "/" + season + "/data.json";
            string raw = Networking.DownloadStringFTP(ftp_directory);

            if (raw == string.Empty) {
                // we are on the first episode of this season, lets create a new object
                return new EpisodeList();
            }


            EpisodeList episodes = JsonConvert.DeserializeObject<EpisodeList>(raw);
            return episodes;
        }

        public static string FixThumbnailRes(string url) {
            return url.Replace("172x255", "370x549");
        }

        public static string FixPosterRes(string url) {
            return url.Replace("353x208", "10000x10000");
        }


        public static EpisodeObject GetTheirEpisodeData(string season) {
            CookieAwareWebClient web = new CookieAwareWebClient();
            web.Cookies = Cookies;

            string url = string.Format(GET_EPISODES, season);
            byte[] response = null;

            web.FlixifyHeaders();
            response = web.DownloadData(url);

            string raw = Encoding.Default.GetString(response);
            EpisodeObject episodeData = JsonConvert.DeserializeObject<EpisodeObject>(raw);

            return episodeData;
        }

    }
}
