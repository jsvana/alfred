# Alfred

Alfred is a server and collection of clients, each written for a different user in mind, that allows for easy and ready access to any and all services we could think of.  Want to get the weather in your town? Ask Alfred.  Need to check the number of players on your favorite Minecraft server? Ask Alfred.  You get the idea.

We are lazy, so we came up with a wonderfully simple way to get the information we want in a simple format.  Data is transferred to and from Alfred via JSON, which makes it very easy to create a new interface for the server.

The project is being actively developed, and new features and clients are being added all the time.  If you have any ideas for either, email the developers at jsvana@mtu.edu and kelwert@mtu.edu.

## Syntax

Commands are sent in JSON to Alfred in the following format:

	{
		"alfred": "0.1",
		"key": "912ec803b2ce49e4a541068d495ab570",
		"method": "Weather.Current",
		"params": {
			"zip": "48195"
		}
	}

`alfred` is the API version,  
`key` is the API key for the session,  
`method` is the method you would like to run, and  
`params` contains any parameters you need to send to the method.

To initiate a session, you send

	{
		"alfred": "0.1",
		"key": "",
		"method": "App.Login",
		"params": {
			"username": "linus",
			"password": "hunter2"
		}
	}

and the server will send you back a reply in the form

	{
		"code": 0,
		"message": "Method success.",
		"data": {
			"key": "fc2baa1a20b4d5190b122b383d7449fd"
		}
	}

if you successfully logged in, or in the form

	{
		"code": -5,
		"message": "Method failed.",
		"data": {
			"message": "Incorrect username or password."
		}
	}

if there was an error.

## Responses

Responses take the form

	{
		"code": -1,
		"message": "Malformed command.",
		"data": { }
	}

If the code is less than zero, the response is an error.  Otherwise, the response is a successful result.

### Results

`0` - `Method success`: the method executed successfully

### Errors

`-1` - `Malformed command`: the command JSON was not formatted correctly  
`-2` - `Unknown command`: the user specified an unknown method  
`-3` - `Not authenticated`: the user has not yet authenticated  
`-4` - `Incorrect parameters`: parameter(s) are invalid or missing  
`-5` - `Method failed`: the method did not execute successfully  
`-6` - `Internal server error`: something went wrong inside of Alfred  

## Commands

### Alfred

**Alfred**.***Login***

Initiates session with the server.

*Parameters:*  
`username (string)`, the username for the user  
`password (string)`, the password for the user  

*Returns:*  
`key (string)`, the API key for the user  

**Alfred**.***Time***

Gets the server time.

*Parameters:* `none`

*Returns:*  
`time (string)`, Alfred's system time, in the format YYYY-mm-dd hh:mm:ss GMT-hh:mm  

### Location

**Location**.***Weather***

Fetches current weather for a given zip code.

*Parameters:*  
`zip (string)`, the zip code for the area  

*Returns:*  
`location (string)`, the city and state for the conditions  
`text (string)`, a description of the conditions  
`temp (string)`, the current temperature (in Celcius)  
`date (string)`, the date of the conditions  

**Location**.***Zip***

Fetches Zip Code of the given city.

*Parameters:*  
`city (string)`, the name of the city in the zip code  

*Returns:*  
`zip (string)`, the zip code of the city  

**Location**.***AreaCode***

Fetches the area code of the given city.

*Parameters:*  
`city (string)`, the name of the city in the area code  

*Returns:*  
`areacode (string)`, the area code of the city  

**Location**.***NearestAirport***

Fetches the closest airport to the given city.

*Parameters:*  
`city (string)`, the name of the city to query  

*Returns:*  
`airport (string)`, the closest airport to the city  

### Minecraft

**Minecraft**.***MOTD***

Gets the MOTD of the given server.

*Parameters:*  
`server (string)`, the Minecraft server to access  

*Returns:*  
`motd (string)`, the message of the day of the Minecraft server  

**Minecraft**.***Players***

Gets the current player count of the given server.

*Parameters:*  
`server (string)`, the Minecraft server to access  

*Returns:*  
`players (string)`, the number of players on the Minecraft server  

**Minecraft**.***MaxPlayers***

Gets the max player count of the given server.

*Parameters:*  
`server (string)`, the Minecraft server to access  

*Returns:*  
`maxPlayers (string)`, the maximum number of players allowed on the Minecraft server  

### Net

**Net**.***Ping***

Pings a host from the server.

*Parameters:*  
`host (string)`, the host to ping  

*Returns:*  
`response (string)`, the ping response from the host  

**Net**.***DNS***

Looks up a host from the server.

*Parameters:*  
`host (string)`, the host to lookup  

*Returns:*  
`response (string)`, the DNS lookup results for the host  

**Net**.***Shorten***

Shortens a given URL.

*Parameters:*  
`url (string)`, the URL to shorten  

*Returns:*  
`url (string)`, the shortened URL  

**Net**.***LMGTFY***

Gives an LMGTFY URL from the given string.

*Parameters:*  
`text (string)`, the text to be included in the URL  

*Returns:*  
`url (string)`, the query URL  

### Net.Bitbucket

**Net.Bitbucket**.***Followers***

Gets the followers of the given Bitbucket user.

*Parameters:*  
`user (string)`, the user to search  

*Returns:*  
`followers (json)`, a JSON array of the followers  

**Net.Bitbucket**.***Status***

Gets Bitbucket's status.

*Parameters:* `none`

*Returns:*  
`time (string)`, the time of the latest update  
`description (string)`, the latest status description  

### Net.Github

**Net.Github**.***Status***

Gets Github's status.

*Parameters:* `none`

*Returns:*  
`time (string)`, the time of the latest update  
`description (string)`, the latest status description  

### Net.Twitter

**Net.Twitter**.***Tweets***

Gets the most recent tweets of the given user.

*Parameters:*  
`user (string)`, the user whose tweets are fetched  

*Returns:*  
`tweets (json)`, the user's most recent tweets  

**Net.Twitter**.***LastTweet***

Gets the most recent tweet of the given user.

*Parameters:*  
`user (string)`, the user whose tweet is fetched  

*Returns:*  
`tweet (string)`, the user's most recent tweet  

### Password

**Password**.***Add***

Adds a password to the password manager.

*Parameters:*  
`site (string)`, the site for which the password is retrieved  
`user (string)`, the user of the password  
`new (string)`, the new password that is added  
`master (string)`, the encryption key and identity verification  

*Returns:*  
`message (string)`, the status of the password insertion  

**Password**.***Retrieve***

Retrieves a password from the password manager.

*Parameters:*  
`site (string)`, the site for which the password is retrieved  
`user (string)`, the user of the password  
`master (string)`, the encryption key and identity verification  

*Returns:*  
`password (string)`, the retrieved password  

### XBMC

**XBMC**.***Pause***

Pauses current stream.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Next***

Skips to next song in queue.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Previous***

Skips to previous song in queue.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Shuffle***

Shuffles Now Playing queue.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Mute***

Mutes XBMC.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Unmute***

Unmutes XBMC.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Up***

Moves XBMC selection up.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Down***

Moves XBMC selection down.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Left***

Moves XBMC selection left.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Right***

Moves XBMC selection right.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Select***

Makes XBMC selection.

*Parameters:* `none`

*Returns:*  
`message (string)`, the result of the command.  

**XBMC**.***Volume***

Sets XBMC volume.

*Parameters:*  
`volume (string)`, the player's new volume  

*Returns:*  
`message (string)`, the result of the command.  

