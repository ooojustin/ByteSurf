using CloudFlareUtilities;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper {
    public class CookieAwareWebClient : WebClient {

        private CookieContainer Cookies = ClearanceHandler._cookies;

        protected override WebRequest GetWebRequest(Uri address) {
            WebRequest request = base.GetWebRequest(address);
            if (request is HttpWebRequest) {
                HttpWebRequest httpRequest = request as HttpWebRequest;
                httpRequest.CookieContainer = Cookies;
            }
            return request;
        }
    }
}