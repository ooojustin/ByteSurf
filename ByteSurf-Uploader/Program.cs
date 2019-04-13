using System;
using System.IO;
using System.Threading;
using JexFlix_Scraper;
using JexFlix_Scraper.Anime;
using JexFlix_Scraper.Anime.Twist.Moe;
using JexFlix_Scraper.Flixify;
using JexFlix_Scraper.Shows;

class Program {

    [STAThread]
    static void Main(string[] args) {

     //   MessageHandler.Start();

        // Anime.Run();


        //Flixify.Run();
         Thread t1 = new Thread(() => Flixify.Run(6));
         t1.Start();
        // Thread t2 = new Thread(() => Flixify.Run(17));
        // t2.Start();
        //Thread t3 = new Thread(() => Flixify.Run(7));
        //t3.Start();
        //Thread t4 = new Thread(() => Flixify.Run(3));
        //t4.Start();

        //Thread showThread = new Thread(() => Shows.Run());
        //showThread.Start();
        // Twist.Run();
        Console.ReadKey();

    }

}