using Newtonsoft.Json;
using OpenQA.Selenium;
using OpenQA.Selenium.Chrome;
using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Text.RegularExpressions;
using System.Threading;
using System.Threading.Tasks;

namespace JexFlix_Scraper.Anime.Misc {

    class CaptchaBypass {

        // Official 2Captcher documentation
        // https://2captcha.com/2captcha-api#solving_recaptchav2_new
        // Code provided by Justin as an example of 2Captcha implementation 
        // https://ghostbin.com/paste/gekkn


        // Response required for post request for captcha authentication
        // Request URL: https://www.masterani.me/captcha/check
        // _token: lkSdPI79Fqxaiaas2d58LHvghOch4sh1GZuAtz55
        // _key: f95e0c233c3b953e1f6d9f1662983394f3e32b49
        // g-recaptcha-response: 03ADlfD19Pzkh4N0_HyYXN-Y2Njv0bsSMxV9E-AtSvMQfRKPJEp4Th2poyZBOZ_btSY1ECgGnDylYVnA695pNSK8dOIQg3vqx8oZ1At2gsuafC8SjlM6Nh5xF0BBPcf_MNrrrvP6G1Fa13NqhpA4p5mtJO7mB9YMXdA7mre6DEG-0ZiSIbhBYTb9M_fpUvSVZZ6qe8FVuXRjpW-Sdxd5kVMAJpYW0E72MOPXzYKDeEXYAtfxI4xMZImDMgP0O9tGSRdGFQX2vgVjx0JR-sNWr_XoRvePtpCpmQaw

        // API Key
        private const string API_KEY = "155a86a595fd9ed836dab1c05b6e77c3";

        // Url to being a 2 captcha request
        // API Key : data site key : pageurl
        private const string MAKE_REQUEST_URL = "http://2captcha.com/in.php?json=1&method=userrecaptcha&key={0}&googlekey={1}&pageurl={2}";

        // Url to check an existing 2captcha request
        private const string CHECK_REQUEST_URL = "http://2captcha.com/res.php?json=1&action=get&key={0}&id={1}";

        // Url to authenticate Captcha on domain
        // Requires (in order) : post token, key, captcha token / response from 2 captcha
        private const string SOLVE_CAPTCHA_URL = "https://www.masterani.me/captcha/check";
        // _token=&_key=&g-recaptcha-response=
        private const string SOLVE_CAPTCHA_PARAMS = "_token={0}&_key={1}&g-recaptcha-response={2}";

        // When the user is presented with a captcha page this is the following layout
        // <form id = "captcha" method="post" action="/captcha/check" class="ui form">
        // <input type = "hidden" name="_token" value="lkSdPI79Fqxaiaas2d58LHvghOch4sh1GZuAtz55">
        // <input type = "hidden" name="_key" value="f95e0c233c3b953e1f6d9f1662983394f3e32b49">
        // <div class="g-recaptcha" data-sitekey="6Lfm6fwSAAAAANmB-uexeNSzC9ycMK9V7a8sJxjr" data-callback="submit"></div>

        /// <summary>
        /// Easy function to check if a page source is a captcha page.
        /// </summary>
        public static bool IsCaptchaPage(string page) {
            return page.IndexOf("captcha") != -1;
        }

        // {"status":0,"request":"ERROR_GOOGLEKEY"}

        public class Request_Json {
            public int status;
            public string request;
        }
        /// <summary>
        /// Returns the data sitekey give the page source using regex
        /// </summary>
        private static string GetDataSiteKey(string page) {

            Regex regex = new Regex("data-sitekey=\".*?\"", RegexOptions.Singleline);

            Match match = regex.Match(page);

            if (match.Success) {
                return match.Value.Split('"')[1];
            }
            // return nothing if nothing was found
            return string.Empty;
        }

        /// <summary>
        /// returns the _token from the submission box if the page is a captcha submission page using regex
        /// </summary>
        private static string GetToken(string page) {

            Regex regex = new Regex("name=\"_token\" value=\".*?\"", RegexOptions.Singleline);

            Match match = regex.Match(page);

            if (match.Success) {
                return match.Value.Split('"')[3];
            }

            return string.Empty;
        }

        /// <summary>
        /// returns _key from the submission form if the page is a captcha page using regex
        /// </summary>
        private static string GetKey(string page) {

            Regex regex = new Regex("name=\"_key\" value=\".*?\"", RegexOptions.Singleline);

            Match match = regex.Match(page);

            if (match.Success) {
                return match.Value.Split('"')[3];
            }

            return string.Empty;
        }

        // String containing the url of page trying to access
        private static string anime_url = "https://www.masterani.me/anime/watch/2991-seishun-buta-yarou-wa-bunny-girl-senpai-no-yume-wo-minai/1";

        // String containing the html content of the page
        private static string anime_page = "";

        public static bool is_solving = false;

        public static void RunCaptchaBypass() {

            // easier to solve a static page.

            is_solving = true;

            // 200 iq play here. 429 ERROR bypass
            // Also need to user a useragent to bypass the robot check.
            // https://stackoverflow.com/questions/26962267/how-do-i-ignore-skip-process-a-httpwebresponse-error-and-still-return-the-json-t
            // Continue reading and ignore error
            using (WebClient cWebClient = General.GetWebClient()) {
                try {
                    anime_page = cWebClient.DownloadString(anime_url);
                } catch (WebException ex) {
                    anime_page = new StreamReader(ex.Response.GetResponseStream()).ReadToEnd();
                }
            }

            if (!IsCaptchaPage(anime_page))
                return;

            bool has_ticket = false;

            string request_id = string.Empty;

            // Create a ticket request.
            while (!has_ticket) {

                request_id = Create2CaptchaRequest(anime_url, anime_page);

                Console.WriteLine(request_id);

                // Check if we have a ticket.
                if (request_id == string.Empty) {
                    // wait for 10 seconds before making another request
                    Thread.Sleep(15 * 1000);
                    // Repeat
                    continue;
                }
                has_ticket = true;
            }

            // make an initial 15 second delay

            bool has_responded = false;

            string response = string.Empty;

            while (!has_responded) {

                response = Check2CaptchaRequest(request_id);

                // {"status":0,"request":"CAPCHA_NOT_READY"}

                if (response == string.Empty) {
                    // wait for 15 seconds before making another request
                    Thread.Sleep(15 * 1000);
                    // Repeat the instruction
                    continue;
                }

                has_responded = true;
            }

#if true
            //http://scraping.pro/example-of-scraping-with-selenium-webdriver-in-csharp/
            //https://www.imagetyperz.com/Forms/bypassrecaptcha_automation.aspx 
            // set g-response-code in page source (with javascript)

            //Hiding the stuff selenium pops up with lol.
            var chromeDriverService = ChromeDriverService.CreateDefaultService();
            chromeDriverService.HideCommandPromptWindow = true;

            // hiding the browser.
            ChromeOptions option = new ChromeOptions();
            option.AddArgument("--headless");

            // submit the captcha.
            using (var driver = new ChromeDriver(chromeDriverService, option)) {
                driver.Navigate().GoToUrl(anime_url);

                IJavaScriptExecutor e = (IJavaScriptExecutor)driver;

                string javascript_code = string.Format("document.getElementById('g-recaptcha-response').innerHTML = '{0}';", response);

                e.ExecuteScript(javascript_code);

                string callback_method = driver.FindElementByClassName("g-recaptcha").GetAttribute("data-callback");


                try {
                    // submit form
                    if (callback_method.Contains("()")) e.ExecuteScript(callback_method);      // execute callback method through javascript
                    else e.ExecuteScript(string.Format("{0}();", callback_method));
                } catch {

                }

                Console.WriteLine("Submitted Captcha");
            }
#endif

            // Finished solving or something has occured
            is_solving = false;
#if false
            // Everything has finished and we will try and bypass the captcha now.
            // WTF WHY WONNT U WORK
            var CaptchaResult = SubmitCaptcha(anime_page, response, anime_url);

            Console.WriteLine(CaptchaResult);
#endif

        }

        /// <summary>
        /// Makes a request to the 2Captcha api and returns the request id in a json format
        /// </summary>
        private static string Create2CaptchaRequest(string url, string page) {

            string get_url = string.Format(MAKE_REQUEST_URL, API_KEY, GetDataSiteKey(page), url);

            try {
                string json_captcha = General.GET(get_url);

                Request_Json data = JsonConvert.DeserializeObject<Request_Json>(json_captcha, General.DeserializeSettings);

                if (data.status == 1) {
                    return data.request;
                }
            } catch { }
            return string.Empty;
        }

        /// <summary>
        /// Makes a request to the 2Captcha API with the existing request and check every 15 seconds if we have a Captcha solved.
        /// </summary>
        private static string Check2CaptchaRequest(string req_id) {

            string get_url = string.Format(CHECK_REQUEST_URL, API_KEY, req_id);

            try {
                string json_captcha = General.GET(get_url);

                Console.WriteLine(json_captcha);

                Request_Json data = JsonConvert.DeserializeObject<Request_Json>(json_captcha, General.DeserializeSettings);

                if (data.status == 1) {
                    return data.request;
                }

            } catch { }

            return string.Empty;

        }

        /// <summary>
        /// Submits the solved captcha to the masterani.me Api to continue browsing
        /// </summary>
        private static string SubmitCaptcha(string page, string solved, string intended_url) {

            string get_params = string.Format(SOLVE_CAPTCHA_PARAMS, GetToken(page), GetKey(page), solved);
#if false
            using (WebClient cWebClient = General.GetWebClient()) {
                try {
                    cWebClient.Headers[HttpRequestHeader.ContentType] = "application/x-www-form-urlencoded";
                    return cWebClient.UploadString(SOLVE_CAPTCHA_URL, get_params);

                } catch (WebException ex) {
                    return new StreamReader(ex.Response.GetResponseStream()).ReadToEnd();
                }   
            }
#else
            Console.WriteLine(SOLVE_CAPTCHA_URL + "?" + get_params);

            return General.POST(SOLVE_CAPTCHA_URL, get_params, intended_url);
#endif

        }
    }
}
