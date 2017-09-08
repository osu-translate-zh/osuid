using System.Net;

namespace osuid_dll
{
    public class osuid
    {
        public int get_userid(string username)
        {
            int userid;
            try
            {
                HttpWebRequest request = WebRequest.Create("http://osu.ppy.sh/users/" + username) as HttpWebRequest;
                request.AllowAutoRedirect = false;
                request.Method = "HEAD";
                HttpWebResponse response = request.GetResponse() as HttpWebResponse;
                string[] location = response.Headers["Location"].Split('/');
                userid = int.Parse(location[location.Length - 1]);
            } catch
            {
                userid = 0;
            }
            return userid;
        }
    }
}
