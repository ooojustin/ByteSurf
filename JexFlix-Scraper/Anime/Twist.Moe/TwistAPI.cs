using System;
using System.Collections.Generic;
using System.Linq;
using System.Net;
using System.Security.Cryptography;
using System.Text;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Twist.Moe {

    class TwistAPI {

        public const string SECRET_KEY = "k8B$B@0L8D$tDYHGmRg98sQ7!%GOEGOX27T"; // Found in javascript

        /// <summary>
        /// This webclient bypasses api restriction
        /// </summary>
        public static WebClient WebClientBypass() {
            WebClient webClient = new WebClient();
            webClient.Proxy = null;
            webClient.Headers.Add("user-agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");            webClient.Headers.Add("user-agent", "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36");
            webClient.Headers.Add("x-access-token", "1rj2vRtegS8Y60B3w3qNZm5T2Q0TN2NR");

            return webClient;
        }



    }
}
