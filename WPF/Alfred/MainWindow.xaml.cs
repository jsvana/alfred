using System;
using System.Collections.Generic;
using System.IO;
using System.Linq;
using System.Net;
using System.Text;
using System.Threading.Tasks;
using System.Windows;
using System.Windows.Controls;
using System.Windows.Data;
using System.Windows.Documents;
using System.Windows.Input;
using System.Windows.Media;
using System.Windows.Media.Imaging;
using System.Windows.Navigation;
using System.Windows.Shapes;
using System.Runtime.Serialization.Json;
using System.Runtime.Serialization;
using Newtonsoft.Json;
using Newtonsoft.Json.Linq;

namespace Alfred
{
    /// <summary>
    /// Interaction logic for MainWindow.xaml
    /// </summary>
    public partial class MainWindow : Window
    {
        private string apiKey = "";
        private string playerID = "";
        private Stack<string> commandStack;
        private int commandOffset = 0;

        private const string ALFRED_HOSTNAME = "psg.mtu.edu";
        private const string ALFRED_PORT = "21516";
        private const string ALFRED_USERNAME = "jsvana";
        private const string ALFRED_PASSWORD = "iorTk3QFcm";
        private const string ALFRED_LOCATION = "/alfred/alfred.rpc";

        public MainWindow()
        {
            InitializeComponent();

            commandStack = new Stack<string>();
            commandStack.Push("");
        }

        private void Window_Loaded(object sender, RoutedEventArgs e)
        {
            input.Focus();

            login(ALFRED_USERNAME, ALFRED_PASSWORD);
        }

        private void Send_Click(object sender, RoutedEventArgs e)
        {
            string command = input.Text;
            commandOffset = 0;

            if (command.Length == 0)
            {
                return;
            }

            string[] words = command.Split(' ');

            string postData = "";
            string retCommand = "";

            switch (words[0])
            {
                case "login":
                    if (words.Length < 3)
                    {
                        result.Text = "Please enter a username and password.";
                        return;
                    }
                    string username = words[1];
                    string password = words[2];

                    postData += "{\"alfred\":\"0.1\",\"method\":\"Alfred.Login\",\"params\":{\"username\":\"" + username + "\",\"password\":\"" + password + "\"}}";
                    retCommand = "Alfred.Login";
                    break;

                case "time":
                    postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Alfred.Time\",\"params\":{ }}";
                    retCommand = "Alfred.Time";
                    break;
                
                case "password":
                    if (words.Length < 2)
                    {
                        result.Text = "Please enter a password command.";
                        return;
                    }

                    string passwordCommand = words[1];
                    switch (passwordCommand)
                    {
                        case "retrieve":
                            if (words.Length < 5)
                            {
                                result.Text = "Please enter a site, username, and your master password.";
                                return;
                            }
                            string site = words[2];
                            string usernameRetrieve = words[3];
                            string masterPassword = words[4];

                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Password.Retrieve\",\"params\":{\"site\":\"" + site + "\",\"username\":\"" + usernameRetrieve + "\",\"master\":\"" + masterPassword + "\"}}";
                            retCommand = "Password.Retrieve";
                            break;
                        case "add":
                            if (words.Length < 6)
                            {
                                result.Text = "Please enter a site, new password, and your master password.";
                                return;
                            }
                            string siteAdd = words[2];
                            string usernameAdd = words[3];
                            string newPassAdd = words[4];
                            string masterAdd = words[5];

                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Password.Add\",\"params\":{\"site\":\"" + siteAdd + "\",\"username\":\"" + usernameAdd + "\",\"new\":\"" + newPassAdd + "\",\"master\":\"" + masterAdd + "\"}}";
                            retCommand = "Password.Add";
                            break;
                        default:
                            result.Text = "Unknown password command.";
                            return;
                    }
                    break;
                case "minecraft":
                    if (words.Length < 2)
                    {
                        result.Text = "Please specify a command.";
                        return;
                    }

                    string minecraftCommand = words[1];

                    switch (minecraftCommand)
                    {
                        case "motd":
                            if (words.Length < 3)
                            {
                                result.Text = "Please specify a server.";
                                return;
                            }
                            string minecraftMOTDServer = words[2];
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.MOTD\",\"params\":{\"server\":\"" + minecraftMOTDServer + "\"}}";
                            retCommand = "Minecraft.MOTD";
                            break;
                        case "players":
                            if (words.Length < 3)
                            {
                                result.Text = "Please specify a server.";
                                return;
                            }
                            string minecraftCountServer = words[2];
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.Players\",\"params\":{\"server\":\"" + minecraftCountServer + "\"}}";
                            retCommand = "Minecraft.Players";
                            break;
                        case "maxplayers":
                            if (words.Length < 3)
                            {
                                result.Text = "Please specify a server.";
                                return;
                            }
                            string minecraftMaxPlayersServer = words[2];
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.MaxPlayers\",\"params\":{\"server\":\"" + minecraftMaxPlayersServer + "\"}}";
                            retCommand = "Minecraft.MaxPlayers";
                            break;
                        default:
                            result.Text = "Unknown Minecraft command.";
                            return;
                    }
                    break;
                case "ping":
                    if (words.Length < 2)
                    {
                        result.Text = "Please enter a host.";
                        return;
                    }

                    string host = words[1];
                    postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Network.Ping\",\"params\":{\"host\":\"" + host + "\"}}";
                    retCommand = "Network.Ping";
                    break;
                case "dns":
                    if (words.Length < 2)
                    {
                        result.Text = "Please enter a host.";
                        return;
                    }

                    string dnsHost = words[1];
                    postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Network.DNS\",\"params\":{\"host\":\"" + dnsHost + "\"}}";
                    retCommand = "Network.DNS";
                    break;
                case "weather":
                    if (words.Length < 2)
                    {
                        result.Text = "Please enter a zip code.";
                        return;
                    }

                    string zip = words[1];
                    postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Location.Weather\",\"params\":{\"zip\":\"" + zip + "\"}}";
                    retCommand = "Location.Weather";
                    break;
                case "xbmc":
                    if (words.Length < 2)
                    {
                        result.Text = "Please enter an XBMC command.";
                        return;
                    }

                    string xbmcCommand = words[1];
                    switch (xbmcCommand)
                    {
                        case "pause":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Pause\",\"params\":{}}";
                            retCommand = "XBMC.Pause";
                            break;
                        case "mute":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Mute\",\"params\":{}}";
                            retCommand = "XBMC.Mute";
                            break;
                        case "unmute":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Unmute\",\"params\":{}}";
                            retCommand = "XBMC.Unmute";
                            break;
                        case "next":
                        case "skip":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Next\",\"params\":{}}";
                            retCommand = "XBMC.Next";
                            break;
                        case "previous":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Previous\",\"params\":{}}";
                            retCommand = "XBMC.Previous";
                            break;
                        case "volume":
                            int bufInt;
                            if (words.Length < 3)
                            {
                                result.Text = "Please specify a volume.";
                            }
                            else if (!Int32.TryParse(words[2], out bufInt) || Int32.Parse(words[2]) < 0 || Int32.Parse(words[2]) > 100)
                            {
                                result.Text = "Please enter a valid volume.";
                            }
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Volume\",\"params\":{\"volume\":" + words[2] + "}}";
                            retCommand = "XBMC.Volume";
                            break;
                        case "shuffle":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Shuffle\",\"params\":{}}";
                            retCommand = "XBMC.Shuffle";
                            break;
                        case "player":
                            postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.GetPlayer\",\"params\":{}}";
                            retCommand = "XBMC.GetPlayer";
                            break;
                        default:
                            result.Text = "Unknown XBMC command.";
                            return;
                    }
                    break;
                default:
                    result.Text = "Unknown command.";
                    break;
            }

            commandStack.Pop();
            commandStack.Push(command);
            commandStack.Push("");

            ASCIIEncoding encoding = new ASCIIEncoding();
            byte[] data = encoding.GetBytes(postData);

            HttpWebRequest HttpWReq = (HttpWebRequest)WebRequest.Create("http://" + ALFRED_HOSTNAME + ":" + ALFRED_PORT + ALFRED_LOCATION);

            HttpWReq.Method = "POST";
            HttpWReq.ContentType = "application/x-www-form-urlencoded";
            HttpWReq.ContentLength = data.Length;

            Stream newStream = HttpWReq.GetRequestStream();
            newStream.Write(data, 0, data.Length);
            newStream.Close();

            WebResponse wr = HttpWReq.GetResponse();

            Stream dataStream = wr.GetResponseStream();
            string text = (new StreamReader(dataStream)).ReadToEnd();

            JObject retJson = JObject.Parse(text);

            if (retJson.SelectToken("code") != null)
            {
                if (Int32.Parse(retJson.SelectToken("code").ToString()) < 0)
                {
                    result.Text = retJson.SelectToken("data").SelectToken("message").ToString();
                }
                else
                {
                    JToken retData = retJson.SelectToken("data");

                    switch (retCommand)
                    {
                        case "Alfred.Login":
                            if (retData.SelectToken("key") != null)
                            {
                                apiKey = retJson.SelectToken("result").SelectToken("key").ToString();
                                result.Text = "Login successful.";
                            }
                            else
                            {
                                result.Text = "Error in logging in.";
                            }
                            break;
                        case "Alfred.Time":
                            if (retData.SelectToken("time") != null)
                            {
                                result.Text = "Alfred's time: " + retData.SelectToken("time").ToString();
                            }
                            else
                            {
                                result.Text = "Error in logging in.";
                            }
                            break;
                        case "Minecraft.MOTD":
                            if (retData.SelectToken("motd") != null)
                            {
                                result.Text = retData.SelectToken("motd").ToString();
                            }
                            else
                            {
                                result.Text = "Error in retrieving server MOTD.";
                            }
                            break;
                        case "Minecraft.Players":
                            if (retData.SelectToken("players") != null)
                            {
                                result.Text = retData.SelectToken("players").ToString();
                            }
                            else
                            {
                                result.Text = "Error in retrieving server player count.";
                            }
                            break;
                        case "Minecraft.MaxPlayers":
                            if (retData.SelectToken("maxPlayers") != null)
                            {
                                result.Text = retData.SelectToken("maxPlayers").ToString();
                            }
                            else
                            {
                                result.Text = "Error in retrieving server max player count.";
                            }
                            break;
                        case "Password.Retrieve":
                            if (retData.SelectToken("password") != null)
                            {
                                result.Text = retData.SelectToken("password").ToString();
                            }
                            else
                            {
                                result.Text = "Error in retrieving password.";
                            }
                            break;
                        case "Password.Add":
                            if (retData.SelectToken("message") != null)
                            {
                                result.Text = retData.SelectToken("message").ToString();
                            }
                            else
                            {
                                result.Text = "Error in adding password.";
                            }
                            break;
                        case "Network.DNS":
                            if (retData.SelectToken("response") != null)
                            {
                                result.Text = retData.SelectToken("response").ToString();
                            }
                            else
                            {
                                result.Text = "Error in host lookup.";
                            }
                            break;
                        case "Network.Ping":
                            if (retData.SelectToken("response") != null)
                            {
                                result.Text = retData.SelectToken("response").ToString();
                            }
                            else
                            {
                                result.Text = "Error pinging host.";
                            }
                            break;
                        case "Location.Weather":
                            if (retData.SelectToken("location") != null && retData.SelectToken("text") != null && retData.SelectToken("temp") != null)
                            {
                                result.Text = "Weather for " + retData.SelectToken("location").ToString() + ": " + retData.SelectToken("temp").ToString() + "\u00b0C, " + retData.SelectToken("text").ToString();
                            }
                            else
                            {
                                result.Text = "Error retrieving weather.";
                            }
                            break;
                        case "XBMC.GetPlayer":
                            if (retData.SelectToken("playerid") != null)
                            {
                                JToken[] players = retData.SelectToken("playerids").ToArray();
                                if (players.Length > 0)
                                {
                                    playerID = players[0].SelectToken("playerid").ToString();
                                    result.Text = "PlayerID: " + playerID;
                                }
                            }
                            else
                            {
                                result.Text = "Error in adding password.";
                            }
                            break;
                        case "XBMC.Pause":
                        case "XBMC.Mute":
                        case "XBMC.Unmute":
                        case "XBMC.Next":
                        case "XBMC.Previous":
                        case "XBMC.Volume":
                        case "XBMC.Shuffle":
                            if (retData.SelectToken("message") != null)
                            {
                                result.Text = retData.SelectToken("message").ToString();
                            }
                            else
                            {
                                result.Text = "Error in sending XBMC command.";
                            }
                            break;
                        default:
                            result.Text = retJson.SelectToken("result").ToString();
                            break;
                    }
                }
            }
            else
            {
                result.Text = "Internal server error.";
            }

            input.Text = "";
        }

        private void Input_KeyDown(object sender, KeyEventArgs e)
        {
            if (e.Key == Key.Return)
            {
                Send_Click(sender, null);
            }
            else if (e.Key == Key.Up)
            {
                if (commandStack.Count == 0)
                {
                    return;
                }

                if (commandOffset < commandStack.Count - 1)
                {
                    ++commandOffset;
                }

                input.Text = commandStack.ToArray<string>()[commandOffset];
                input.CaretIndex = input.Text.Length;
            }
            else if (e.Key == Key.Down)
            {
                if (commandStack.Count == 0)
                {
                    return;
                }

                if (commandOffset > 0)
                {
                    --commandOffset;
                }

                input.Text = commandStack.ToArray<string>()[commandOffset];
                input.CaretIndex = input.Text.Length;
            }
        }

        private void login(string username, string password)
        {
            string command = "{\"alfred\":\"0.1\",\"key\":\"\",\"method\":\"Alfred.Login\",\"params\":{\"username\":\"" + username + "\",\"password\":\"" + password + "\"}}";

            ASCIIEncoding encoding = new ASCIIEncoding();
            byte[] data = encoding.GetBytes(command);

            HttpWebRequest HttpWReq = (HttpWebRequest)WebRequest.Create("http://" + ALFRED_HOSTNAME + ":" + ALFRED_PORT + ALFRED_LOCATION);

            HttpWReq.Method = "POST";
            HttpWReq.ContentType = "application/x-www-form-urlencoded";
            HttpWReq.ContentLength = data.Length;

            Stream newStream = HttpWReq.GetRequestStream();
            newStream.Write(data, 0, data.Length);
            newStream.Close();

            WebResponse wr = HttpWReq.GetResponse();

            Stream dataStream = wr.GetResponseStream();
            string text = (new StreamReader(dataStream)).ReadToEnd();

            JObject retJson = JObject.Parse(text);

            if (retJson.SelectToken("code") != null)
            {
                if (Int32.Parse(retJson.SelectToken("code").ToString()) < 0)
                {
                    result.Text = retJson.SelectToken("data").SelectToken("message").ToString();
                }
                else
                {
                    if (retJson.SelectToken("data").SelectToken("key") != null)
                    {
                        apiKey = retJson.SelectToken("data").SelectToken("key").ToString();
                        result.Text = "Login successful.";
                    }
                    else
                    {
                        result.Text = "Error loggin in.";
                    }
                }
            }
            else
            {
                result.Text = "Internal server error.";
            }
        }
    }
}
