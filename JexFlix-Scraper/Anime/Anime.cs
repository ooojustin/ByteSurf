using JexFlix_Scraper.Anime.MasterAnime;
using JexFlix_Scraper.Anime.MasterAnime.Scraper;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

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
                    if (AnimeInfo.info.synopsis != null) UploadData.synopsis = AnimeInfo.info.synopsis;
                    UploadData.thumbnail = anime.GetThumbnail();
                    UploadData.preview = AnimeInfo.GetWallpaper();
                    UploadData.episode_length = AnimeInfo.info.episode_length;

                    foreach (AniInfo.Genre genre in AnimeInfo.genres) {
                        if (genre.name != null) UploadData.genres.Add(genre.name);
                    }


                    foreach (AniInfo.EpisodeData EpisodeInfo in AnimeInfo.episodes) {
                        Console.WriteLine("title: " + EpisodeInfo.info.title);
                        EpisodeData EpData = new EpisodeData();
                        EpData.description = EpisodeInfo.info.description;
                        EpData.thumbnail = AnimeInfo.GetThumbnail(Convert.ToInt32(EpisodeInfo.info.episode) - 1);
                        EpData.duration = EpisodeInfo.info.duration;
                        EpData.episode = EpisodeInfo.info.episode;

                        // Fill the mirrors

                        AniEpisode episode = AnimeInfo.GetEpisode(EpisodeInfo);

                        if (episode == null)
                            return;

                        bool UltraHd = false;
                        bool Hd = false;
                       // bool Standard = false;

                        foreach (AniEpisode.Mirror mirror in episode.EmbedList) {

                            if (MirrorParser.IsSupported(mirror)) {

                                if (mirror.quality == 1080 && !UltraHd) {
                                    Action<string> callback = (s) => {
                                        Quality quality = new Quality();
                                        quality.resolution = 1080;
                                        quality.link = "";
                                        EpData.qualities.Add(quality);
                                        UltraHd = true;
                                        // Networking.ReuploadRemoteFile(quality.link, )
                                    };
                                    new MirrorParser(mirror, callback).Run();
                                }

                                if (mirror.quality == 720 && !Hd) {
                                    Action<string> callback = (s) => {
                                        Quality quality = new Quality();
                                        quality.resolution = 720;
                                        quality.link = s;
                                        EpData.qualities.Add(quality);
                                        Hd = true;
                                    };
                                    new MirrorParser(mirror, callback).Run();
                                }

                                //if (mirror.quality == 480 && !Standard) {
                                //    Action<string> callback = (s) => {
                                //        Quality quality = new Quality();
                                //        quality.resolution = 480;
                                //        quality.link = s;
                                //        EpData.qualities.Add(quality);
                                //        Standard = true;
                                //    };

                                //   new MirrorParser(mirror, callback).Run();
                                //}

                            }

                        }
                        UploadData.episodeData.Add(EpData);
                    }

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

                    Console.WriteLine(JsonConvert.SerializeObject(UploadData));
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