using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Misc {

    class General {

        /// <summary>
        /// Gets an instance of the WebClient class.
        /// </summary>
        public static WebClient GetWebClient() {
            WebClient webClient = new WebClient();
            webClient.Proxy = null;
            webClient.Headers.Add("user-agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            return webClient;
        }

        /// <summary>
        /// Simple Post request using webClient
        /// </summary>
        public static string POST(string url, string post_param, string referer) {
            try {
                using (WebClient webClient = GetWebClient()) {
                    webClient.Headers[HttpRequestHeader.Referer] = referer;
                    return webClient.UploadString(url, post_param);
                }

            } catch (WebException ex) {
                return new StreamReader(ex.Response.GetResponseStream()).ReadToEnd();
                // return "ERROR: " + ex.Message;
            }

        }

        /// <summary>
        /// Makes a simple GET request to a URL given in the paramater.
        /// </summary>      
        public static string GET(string url) {
            try {
                using (WebClient webClient = GetWebClient())
                    return webClient.DownloadString(url);
            } catch (Exception ex) {
                return "ERROR: " + ex.Message;
            }
        }

        /// <summary>
        ///  Gets redirected URL src = https://stackoverflow.com/questions/704956/getting-the-redirected-url-from-the-original-url
        /// </summary>
        public static string RedirectedURL(string url) {

            string newUrl = url;

            for (var i = 0; i < 8; ++i) {

                HttpWebRequest req = null;
                HttpWebResponse resp = null;
                try {
                    req = (HttpWebRequest)HttpWebRequest.Create(url);
                    req.Method = "GET";
                    req.AllowAutoRedirect = false;
                    resp = (HttpWebResponse)req.GetResponse();
                    switch (resp.StatusCode) {
                        case HttpStatusCode.OK:
                            return newUrl;
                        case HttpStatusCode.Redirect:
                        case HttpStatusCode.MovedPermanently:
                        case HttpStatusCode.RedirectKeepVerb:
                        case HttpStatusCode.RedirectMethod:
                            newUrl = resp.Headers["Location"];
                            if (newUrl == null)
                                return url;

                            if (newUrl.IndexOf("://", System.StringComparison.Ordinal) == -1) {
                                // Doesn't have a URL Schema, meaning it's a relative or absolute URL
                                Uri u = new Uri(new Uri(url), newUrl);
                                newUrl = u.ToString();
                            }
                            break;
                        default:
                            return newUrl;
                    }
                    url = newUrl;
                } catch (WebException) {
                    // Return the last known good URL
                    return newUrl;
                } catch (Exception) {
                    return null;
                } finally {
                    if (resp != null)
                        resp.Close();
                }

            }

            return newUrl;

        }

        /// <summary>
        /// Deserializer settings for handling NULL objects.
        /// </summary>
        public static JsonSerializerSettings DeserializeSettings = new JsonSerializerSettings {
            NullValueHandling = NullValueHandling.Ignore,
            MissingMemberHandling = MissingMemberHandling.Ignore
        };

    }
}
