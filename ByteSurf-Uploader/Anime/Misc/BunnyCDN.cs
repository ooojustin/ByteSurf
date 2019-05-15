using System;
using System.Collections.Generic;
using System.Linq;
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

        private static void CreateAPIRequest(string path ,CDNMethod method = CDNMethod.POST) {

        }
        public static void PurgeCDNLink() {

        }
    }
}
