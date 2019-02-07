using JexFlix_Scraper.Anime.Misc;
using Newtonsoft.Json;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.MasterAnime {

    public class AniEpisode {

        /// <summary>
        /// Format URL to find specific video link.
        /// </summary>
        private const string EPISODE_URL = "https://www.masterani.me/anime/watch/{0}/{1}";


        /// <summary>
        /// Expression used to locate json video regarding where episodes are streamed from.
        /// </summary>
        private const string VIDEO_MIRRIORS_EXPRESSION = "<video-mirrors :mirrors='.*?'></video-mirrors>";

        public static AniEpisode Create(AniInfo anime, AniInfo.EpisodeData data) {

            string url = string.Format(EPISODE_URL, anime.info.slug, data.info.episode);

            string raw = string.Empty;

            using (WebClient cWebClient = General.GetWebClient()) {
                try {
                    raw = cWebClient.DownloadString(url);
                } catch (WebException ex) {
                    raw = new StreamReader(ex.Response.GetResponseStream()).ReadToEnd();
                }
            }

            Captcha_check:
     
            // Captcha occurances only appear here so everytime we load this we can run the captcha bypass.
            if (CaptchaBypass.IsCaptchaPage(raw)) {
                if (CaptchaBypass.is_solving == false) {
                    Thread t = new Thread(() => {
                        CaptchaBypass.RunCaptchaBypass();
                    });
                    t.IsBackground = true;
                    t.Start();
                }

                goto Captcha_check;
            }

            Regex regex = new Regex(VIDEO_MIRRIORS_EXPRESSION, RegexOptions.Singleline);
            Match match = regex.Match(raw);
            if (!match.Success)
                return null;

            string jsonData = Regex.Unescape(match.Value.Split('\'')[1]);
            List<Mirror> mirrors = JsonConvert.DeserializeObject<List<Mirror>>(jsonData, General.DeserializeSettings);

            AniEpisode animeEpisode = new AniEpisode();
            animeEpisode.EmbedList = mirrors;
            return animeEpisode;

        }

        public List<Mirror> EmbedList;

        public class Mirror {

            public int id;
            public int host_id;
            public string embed_id;
            public int quality;
            public int type;
            public Host host;

            public string GetLanguageType() {
                switch (type) {
                    case 1:
                        return "Sub";

                    case 2:
                        return "Dub";

                    default:
                        return "";
                }
            }

            public string GetURL() {
                return host.embed_prefix + embed_id + host.embed_suffix;
            }

            public override string ToString() {
                return string.Format("{0} - {1}p - {2}", host.name, quality, GetLanguageType());
            }

        }

        public class Host {

            public int id;
            public string name;
            public string embed_prefix;
            public string embed_suffix;

        }

    }
}
