﻿using CloudFlareUtilities;
using JexFlix_Scraper.Flixify;
using SafeRequest;
using System;
using System.Collections.Specialized;
using System.IO;
using System.Net;
using System.Net.Http;

namespace JexFlix_Scraper {

    public static class Networking {

        /// <summary>
        /// Public link to access files uploaded to the JexFlix FTP/CDN server.
        /// </summary>
        public const string CDN_URL = "https://cdn.jexflix.com";

        /// <summary>
        /// Credentials to CDN hosting FTP server.
        /// Used to upload movies and whatnot owo.
        /// </summary>
        private static NetworkCredential FTP_CREDENTIALS = new NetworkCredential("jexflix", "ce726c9e-edcc-4adb-839edc6148bb-7807-4e03");

        /// <summary>
        /// Encryption key used to protect data transferred between scraper and scraper.jexflix.com server.
        /// </summary>
        private const string ENCRYPTION_KEY = "jexflix";

        /// <summary>
        /// SafeRequest class, operates as a constant - should not be modified.
        /// </summary>
        public static SafeRequest.SafeRequest SAFE_REQUEST = new SafeRequest.SafeRequest(ENCRYPTION_KEY);

        /// <summary>
        /// Common User-Agent accepted by all websites.
        /// Must be added to some web requests to prevent them from being blocked.
        /// </summary>
        public const string USER_AGENT = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Safari/537.36";

        /// <summary>
        /// Bypass CloudFlare on a specified domain.
        /// https://github.com/elcattivo/CloudFlareUtilities
        /// </summary>
        public static void BypassCloudFlare(string domain, out CookieContainer cookies) {
            ClearanceHandler handler = new ClearanceHandler();
            HttpClient client = new HttpClient(handler);
            client.DefaultRequestHeaders.Add("User-Agent", USER_AGENT);
            client.GetStringAsync(domain);
            cookies = ClearanceHandler._cookies;
        }

        public static void ReuploadRemoteFile(string url, string directory, string file, WebClient web = null) {

            // initialize webclient if we weren't provided with one
            if (web == null) {
                web = new WebClient();
                web.Headers.Add(HttpRequestHeader.UserAgent, USER_AGENT);
            }

            // download the original file
            string localPath = Path.GetTempFileName();
            web.DownloadFile(url, localPath);

            // reupload file to server
            UploadFile(localPath, directory, file);

            // delete the file that was stored locally
            File.Delete(localPath);

        }

        public static void UploadFile(string localPath, string directory, string file) {

            try {
                FtpWebRequest mkdir = GetFTPRequest("ftp://storage.bunnycdn.com" + directory, WebRequestMethods.Ftp.MakeDirectory);
                FtpWebResponse response = (FtpWebResponse)mkdir.GetResponse();
            } catch (Exception ex) {
                if (!ex.Message.Contains("directory already exists"))
                    return;
            }

            // create request to upload file
            string createURI = string.Format("ftp://storage.bunnycdn.com{0}/{1}", directory, file);
            FtpWebRequest request = GetFTPRequest(createURI, WebRequestMethods.Ftp.UploadFile);

            using (Stream fileStream = File.OpenRead(localPath)) {
                using (Stream ftpStream = request.GetRequestStream()) {
                    byte[] buffer = new byte[1024 * 1024];
                    double totalReadBytesCount = 0;
                    int readBytesCount;
                    while ((readBytesCount = fileStream.Read(buffer, 0, buffer.Length)) > 0) {
                        ftpStream.Write(buffer, 0, readBytesCount);
                        totalReadBytesCount += readBytesCount;
                        double progress = (totalReadBytesCount / fileStream.Length) * 100.0;
                        Console.Write("\rUploading {0}: {1}%   ", file, progress.ToString("F"));
                    }
                }
                Console.WriteLine("\rSuccessfully uploaded: " + file);
            }

        }

        public static FtpWebRequest GetFTPRequest(string uri, string method) {
            FtpWebRequest request = (FtpWebRequest)WebRequest.Create(uri);
            request.Method = method;
            request.Credentials = FTP_CREDENTIALS;
            request.Proxy = new WebProxy();
            return request;
        }

        public static bool FileExists(string title) {
            SAFE_REQUEST.UserAgent = "jexflix-client";
            NameValueCollection values = new NameValueCollection();
            values["url"] = title;
            Response response = SAFE_REQUEST.Request("https://scraper.jexflix.com/movie_exists.php", values);
            bool exists = response.GetData<bool>("exists");
            return exists;
        }
         
        public static string Sanitized(this string path) {
            string invalid = "|:@";
            foreach (char c in invalid)
                path = path.Replace(c.ToString(), "");
            return path;
        }

    }

}