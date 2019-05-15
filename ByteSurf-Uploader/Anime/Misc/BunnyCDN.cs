using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Misc
{
    public class BunnyCDN
    {
        private const string API_KEY = "980938c9-68a9-47ed-8d4b-ea0f99892a75cea8726f-cbed-4272-a173-bb94d80044b9";

        enum CDNMethod
        {
            GET = 0,
            POST
        }

        private static void CreateAPIRequest(string path, CDNMethod method = CDNMethod.POST, string data = "") {
            switch (method) {
                case CDNMethod.POST:
                    try {
                        using (WebClient webClient = General.GetWebClient()) {
                            //  return webClient.UploadString(url, post_param);
                        }

                    } catch (WebException ex) {
                        string exception_raw = new StreamReader(ex.Response.GetResponseStream()).ReadToEnd();
                        Console.WriteLine("[CreateAPIRequest] " + exception_raw);
                        // return "ERROR: " + ex.Message;
                    }

                    return;
                case CDNMethod.GET:

                    return;
            }
           
        }
        public static void PurgeCDNLink(string url) {
            CreateAPIRequest("purge");
        }
    }
}
