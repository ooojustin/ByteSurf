using JexFlix_Scraper.Anime.MasterAnime;
using JexFlix_Scraper.Anime.MasterAnime.Scraper;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using JexFlix_Scraper.Anime.Misc;
using System.IO;
using System.Net;

namespace JexFlix_Scraper.Anime {

    public static class Anime {

        /// <summary>
        /// Main function for executing code for the anime scraper.
        /// </summary>
        public static void Run() {

            AniSearch InitialAnime = AniSearch.GetAnime();

            List<AniSearch> AllAnime = new List<AniSearch>();

            AllAnime.Add(InitialAnime);

            Console.WriteLine("There are: " + InitialAnime.last_page + " pages");

            // for (int i = 2; i <= InitialAnime.last_page; i++) {
            //    Console.WriteLine("On " + i + " page");
            //    AniSearch CurrentAnime = AniSearch.GetAnime(page: i);
            //    AllAnime.Add(CurrentAnime);
            //  }


            // Dumps out all the anime that exists.
            // using (System.IO.StreamWriter file = new System.IO.StreamWriter("AnimeFound.txt", true)) {

            foreach (AniSearch animeFound in AllAnime) {

                foreach (AniSearch.Show anime in animeFound.data) {
                    AniInfo AnimeInfo = anime.GetAnime();


                    AniUpload UploadData = new AniUpload();

                    UploadData.title = anime.title;

                    UploadData.url = Slugify(UploadData.title);

                    if (AnimeInfo.info.synopsis != null)
                        UploadData.synopsis = AnimeInfo.info.synopsis;

                    UploadData.episode_length = AnimeInfo.info.episode_length;

                    // Upload this to the CDN then change the link to that and set the link 

                    // cdn.jexflix.com/anime/anime-name-here/thumbnail.jpg 
                    // Networking.CDN_URL + rootObject.item.url + "/thumbnail.jpg";
                    Networking.ReuploadRemoteFile(anime.GetThumbnail(), "/anime/" + UploadData.url, "thumbnail.jpg", UploadData.title, General.GetWebClient());
                    UploadData.thumbnail = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "thumbnail.jpg";

                    Networking.ReuploadRemoteFile(AnimeInfo.GetWallpaper(), "/anime/" + UploadData.url, "preview.jpg", UploadData.title, General.GetWebClient());
                    UploadData.preview = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + "preview.jpg";

                    foreach (AniInfo.Genre genre in AnimeInfo.genres) {
                        if (genre.name != null) UploadData.genres.Add(genre.name);
                    }


                    foreach (AniInfo.EpisodeData EpisodeInfo in AnimeInfo.episodes) {

                        // Get the anime title and send a request to the database to check if it already exists.
                        // If the anime title exists then we will continue to the next anime. repeat untill we dont have an anime and continue
                        // The upload from there

                        Console.WriteLine("title: " + EpisodeInfo.info.title);

                        EpisodeData EpData = new EpisodeData();

                        EpData.episode = Convert.ToInt32(EpisodeInfo.info.episode);

                        EpData.description = EpisodeInfo.info.description;

                        // Upload to the CDN then delete
                        // cdn.jexflix.com/anime/anime-name-here/1/thumbnail.jpg and set the link
                        Networking.ReuploadRemoteFile(AnimeInfo.GetThumbnail(Convert.ToInt32(EpisodeInfo.info.episode) - 1), "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "thumbnail.jpg", UploadData.title, General.GetWebClient());
                        EpData.thumbnail = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "thumbnail.jpg";

                        EpData.duration = AnimeInfo.info.episode_length;


                        // Fill the mirrors
                        AniEpisode episode = AnimeInfo.GetEpisode(EpisodeInfo);

                        if (episode == null)
                            return;

                        bool UltraHd = false;
                        bool Hd = false;
                        // bool Standard = false;

                        foreach (AniEpisode.Mirror mirror in episode.EmbedList) {

                            if (MirrorParser.IsSupported(mirror)) {

                                // Very ghetto fix to get the first mirror of a quality so we dont reupload videos of the same qualities
                                /*
                                  Upload in this format
                                  cdn.jexflix.com/anime/anime-name-here/1/1080.mp4
                                */

                                if (mirror.quality == 1080 && !UltraHd) {
                                    Action<string> callback = (s) => {
                                        Quality quality = new Quality();
                                        quality.resolution = 1080;

                                        // Upload to CDN then delete.
                                        Networking.ReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "1080.mp4", UploadData.title, General.GetWebClient());

                                        // Now update the link
                                        quality.link = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "1080.mp4";

                                        EpData.qualities.Add(quality);
                                        UltraHd = true;
                                    };
                                    new MirrorParser(mirror, callback).Run();
                                }


                                // cdn.jexflix.com/anime/anime-name-here/1/720.mp4

                                if (mirror.quality == 720 && !Hd) {
                                    Action<string> callback = (s) => {
                                        Quality quality = new Quality();
                                        quality.resolution = 720;

                                        // Upload to CDN then delete.
                                        Networking.ReuploadRemoteFile(s, "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode, "720.mp4", UploadData.title, General.GetWebClient());

                                        // Now update the link
                                        quality.link = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + EpisodeInfo.info.episode + "/" + "720.mp4";          
                                      
                                        EpData.qualities.Add(quality);

                                        Hd = true;
                                    };
                                    new MirrorParser(mirror, callback).Run();
                                }

#if false
                                if (mirror.quality == 480 && !Standard) {
                                   Action<string> callback = (s) => {
                                       Quality quality = new Quality();
                                       quality.resolution = 480;
                                       quality.link = s;
                                       EpData.qualities.Add(quality);
                                       Standard = true;
                                   };

                                  new MirrorParser(mirror, callback).Run();
                               }
#endif

                            }

                        }

                        UploadData.episodeData.Add(EpData);

                        // After each episode is updated -> Upload to the CDN
                        string jsonData = JsonConvert.SerializeObject(UploadData);

                        string localPath = Path.GetTempFileName();

                        //open file stream
                        using (StreamWriter file = File.CreateText(localPath)) {

                            JsonSerializer serializer = new JsonSerializer();
                            //serialize object directly into file stream
                            serializer.Serialize(file, jsonData);
                        }

                        // Upload the file to the CDN
                        Networking.UploadFile(localPath, "/anime/" + UploadData.url, UploadData.url + ".json", UploadData.title);

                        // Now update the link
                        string CDNLink = Networking.CDN_URL + "/anime/" + UploadData.url + "/" + UploadData.url + ".json";

                        // Remove the file
                        File.Delete(localPath);


                        AniDb dbinfo = new AniDb();

                        dbinfo.name = UploadData.title;
                        dbinfo.episode_data = CDNLink;

                        // Update the database.
                        using (WebClient Web = General.GetWebClient()) {
                            Web.UploadString("https://scraper.jexflix.com/add_movie.php", JsonConvert.SerializeObject(dbinfo));
                        }

                    }

#if false
                    Console.WriteLine("Title: " + UploadData.title);
                    Console.WriteLine("Description: " + UploadData.synopsis);
                    Console.WriteLine("Preview: " + UploadData.preview);
                    Console.WriteLine("Thumbnail: " + UploadData.thumbnail);
                    Console.WriteLine("URL: " + UploadData.url);

                    foreach (var genre in UploadData.genres) {
                        Console.WriteLine(genre);
                    }

                    foreach (var ed in UploadData.episodeData) {

                        Console.WriteLine("Episode: " + ed.episode);
                        Console.WriteLine("Episode description: " + ed.description);
                        Console.WriteLine("Episode thumbnail: " + ed.thumbnail);
                        Console.WriteLine("Episode duration: " + AnimeInfo.info.episode_length);

                        foreach (var q in ed.qualities) {
                            Console.WriteLine("Episode link: " + q.link);
                            Console.WriteLine("Episode resolution: " + q.resolution);
                        }
                    }
#endif
                    // Move on and repeat to the text episode / anime

                }
            }


        }


        /// <summary>
        /// Fast Paste https://stackoverflow.com/questions/3275242/how-do-you-remove-invalid-characters-when-creating-a-friendly-url-ie-how-do-you
        /// </summary>
        public static string RemoveAccent(this string txt) {
            byte[] bytes = System.Text.Encoding.GetEncoding("Cyrillic").GetBytes(txt);
            return System.Text.Encoding.ASCII.GetString(bytes);
        }

        /// <summary>
        /// Fast Paste https://stackoverflow.com/questions/3275242/how-do-you-remove-invalid-characters-when-creating-a-friendly-url-ie-how-do-you
        /// </summary>
        public static string Slugify(this string phrase) {
            string str = phrase.RemoveAccent().ToLower();
            str = System.Text.RegularExpressions.Regex.Replace(str, @"[^a-z0-9\s-]", ""); // Remove all non valid chars          
            str = System.Text.RegularExpressions.Regex.Replace(str, @"\s+", " ").Trim(); // convert multiple spaces into one space  
            str = System.Text.RegularExpressions.Regex.Replace(str, @"\s", "-"); // //Replace spaces by dashes
            return str;
        }

    }



}
 