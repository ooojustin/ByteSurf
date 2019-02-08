using System;
using System.IO;
using System.Threading;
using JexFlix_Scraper;
using JexFlix_Scraper.Anime;
using JexFlix_Scraper.Flixify;  

class Program {

    static void Main(string[] args) {

        MessageHandler.Start();

        //Anime.Run();
        Flixify.Run();

        Console.ReadKey();

    }

}