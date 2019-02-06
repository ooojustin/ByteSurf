using System;
using System.IO;
using JexFlix_Scraper.Flixify;

class Program {

    static void Main(string[] args) {

        foreach (char c in Path.GetInvalidPathChars()) {
            Console.WriteLine(c);
        }
        Console.ReadKey();

        Flixify.Run();

        Console.ReadKey();

    }

}