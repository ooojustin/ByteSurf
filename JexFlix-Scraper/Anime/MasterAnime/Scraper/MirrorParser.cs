﻿using JexFlix_Scraper.Anime.Misc;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading;
using System.Threading.Tasks;
using System.Windows.Forms;

namespace JexFlix_Scraper.Anime.MasterAnime.Scraper {

    public class MirrorParser {
        private AniEpisode.Mirror mirror;
        private Action<string> callback;

        public MirrorParser(AniEpisode.Mirror mirror, Action<string> callback) {
            this.mirror = mirror;
            this.callback = callback;
        }

        public void Run() {

           //  Thread thread = new Thread(() => {
                switch (mirror.host.name) {
                    case "Streamango":
                        RunBrowser(StreamangoLoaded);
                        break;
                    case "Stream.moe":
                        RunStreamMoe();
                        break;
                    case "Rapidvideo":
                        RunRapidVideo();
                        break;
                    case "MP4Upload":
                        RunMP4Upload();
                        break;
                    default:
                        throw new NotImplementedException("Unsupported host: " + mirror.host.name);
                // }
            }// );

           // thread.SetApartmentState(ApartmentState.STA);
           // thread.Start();

        }

        private void RunBrowser(WebBrowserDocumentCompletedEventHandler loaded) {
            WebBrowser browser = new WebBrowser();
            browser.ScriptErrorsSuppressed = true;
            browser.DocumentCompleted += loaded;
            browser.Navigate(mirror.GetURL());
            Application.Run();
        }

        private void StreamangoLoaded(object sender, WebBrowserDocumentCompletedEventArgs e) {
            WebBrowser browser = (WebBrowser)sender;
            dynamic information = browser.Document.InvokeScript("eval", new object[] { "srces[0]" });
            try {
                string src = General.RedirectedURL("https:" + information.src);
                callback(src);
            } catch (Exception) { }
        }

        private void RunStreamMoe() {
            using (WebClient web = General.GetWebClient()) {
                string page = web.DownloadString(mirror.GetURL());
                Regex regexEnc = new Regex("atob\\('.*?'\\);", RegexOptions.Singleline);
                Match matchEnc = regexEnc.Match(page);
                if (!matchEnc.Success) return;
                string encoded = matchEnc.Value.Split('\'')[1];
                string decoded = Encoding.ASCII.GetString(Convert.FromBase64String(encoded));
                Regex regexDec = new Regex("<source src=\".*?\" type=\"video\\/mp4\">", RegexOptions.Singleline);
                Match matchDec = regexDec.Match(decoded);
                if (!matchDec.Success) return;
                string redirectsrc = matchDec.Value.Split('"')[1];
                string src = General.RedirectedURL(redirectsrc);
                callback(src);
            }
        }

        private void RunRapidVideo() {
            using (WebClient web = General.GetWebClient()) {
                string page = web.DownloadString(mirror.GetURL());
                Regex regex = new Regex("<source src=\".*?\"", RegexOptions.Singleline);
                Match match = regex.Match(page);
                string src = match.Value.Split('"')[1];
                callback(src);
            }
        }

        private void RunMP4Upload() {
            using (WebClient web = General.GetWebClient()) {
                string page = web.DownloadString(mirror.GetURL());
                Regex regex = new Regex("function\\(p.*?\\)\\)", RegexOptions.Singleline);
                Match match = regex.Match(page);
                if (match.Success) {
                    regex = new Regex("\"file\":\".*?\"", RegexOptions.Singleline);
                    string evaluated_js = ScriptEngine.Eval("jscript", match.Value).ToString();
                    Match match_deobf = regex.Match(evaluated_js);
                    if (match_deobf.Success) {
                        callback(match_deobf.Value.Split('"')[3]);
                    }
                }
            }
        }

        public static bool IsSupported(AniEpisode.Mirror mirror) {
            switch (mirror.host.name) {
                case "Streamango":
                case "Stream.moe":
                case "Rapidvideo":
                case "MP4Upload":
                    return true;
                default:
                    return false;
            }
        }
    }

}
