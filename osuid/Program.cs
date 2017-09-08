using System;

namespace osuid
{
    class Program
    {
        static void Main(string[] args)
        {
            try
            {
                Console.Write("Enter Username:");
                string username = Console.ReadLine();
                if (string.IsNullOrEmpty(username))
                {
                    Console.WriteLine("Please Enter Your Username.");
                }
                else
                {
                    int userid = new osuid_dll.osuid().get_userid(username);
                    if (userid != 0)
                    {
                        Console.WriteLine(string.Format("{0}'s ID:{1}",username,userid));
                    } else
                    {
                        Console.WriteLine("User Not Found!");
                    }
                }
            } catch (Exception e)
            {
                // Holy shit! This is a Error!
                Console.WriteLine("We're sorry, an error has occurred!\n" + e);
                Console.ReadKey(true);
            }
        }
    }
}
