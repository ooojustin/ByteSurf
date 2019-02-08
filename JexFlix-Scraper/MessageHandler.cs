using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading;
using System.Threading.Tasks;


namespace JexFlix_Scraper {

    class MessageHandler {

        private static List<Message> MessageQueue = new List<Message>();

        public static void Add(string pre_message, string message, ConsoleColor pre_message_color, ConsoleColor message_color, string tag) {
            Message m = new Message();
            m.pre_message = pre_message;
            m.message = message;
            m.pre_message_color = pre_message_color;
            m.message_color = message_color;
            m.tag = tag;
            MessageQueue.Add(m);
        }

        public static void Start() {
            Thread queueThread = new Thread(Queue);
            queueThread.IsBackground = true;
            queueThread.Start();
        }

        private static bool FirstMessage = true;

        private static void Queue() {
            while (true) {
                List<Message> list = MessageQueue.ToList();
                foreach (Message m in list) {

                    if (!FirstMessage)
                        Console.WriteLine(string.Empty);
                    else FirstMessage = false;

                    Console.ForegroundColor = ConsoleColor.Magenta;
                    Console.Write(" [" + DateTime.Now.ToShortTimeString() + " - " + m.tag + "]");
                    Console.ForegroundColor = m.pre_message_color;
                    Console.Write(" [" + m.pre_message + "] ");
                    Console.ForegroundColor = m.message_color;
                    Console.Write(m.message);
                    MessageQueue.Remove(m);

                }
                Thread.Sleep(500);
            }
        }

    }

    struct Message {
        public string pre_message;
        public string message;
        public ConsoleColor pre_message_color;
        public ConsoleColor message_color;
        public string tag;
    }
}