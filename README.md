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

## Commands

### Alfred

**Alfred.Login**

Initiates session with the server.

*Parameters:*  
	`username`, the username for the user  
	`password`, the password for the user

*Returns:* `key (string)`

**Alfred.Time**

Gets the server time.

*Parameters:* `none`

*Returns:* `time (string, formatted YYYY-mm-dd hh:mm:ss GMT-hh:mm)`

### Location

**Location.Weather**

Fetches current weather for a given zip code.

*Parameters:* `zip`, the zip code for the area

*Returns:*  
	`location`, the city and state for the conditions  
	`text`, a description of the conditions  
	`temp`, the current temperature (in Celcius)  
	`date`, the date of the conditions  

### Minecraft

**Minecraft.MOTD**

Gets the MOTD of the given server.

*Parameters:* `server`

*Returns:* `motd (string)`

**Minecraft.Players**

Gets the current player count of the given server.

*Parameters:* `server`

*Returns:* `players (string)`

**Minecraft.MaxPlayers**

Gets the max player count of the given server.

*Parameters:* `server`

*Returns:* `maxPlayers (string)`

### Net

**Net.Ping**

Pings a host from the server.

*Parameters:* `host`, the host to ping

*Returns:* `response (string)`

**Net.DNS**

Looks up a host from the server.

*Parameters:* `host`, the host to lookup

*Returns:* `response (string)`

**Net.Shorten**

Shortens a given URL.

*Parameters:* `url`, the URL to shorten

*Returns:* `url (string)`

**Net.LMGTFY**

Gives an LMGTFY URL from the given string.

*Parameters:* `text`, the text to query

*Returns:* `url (string)`

### Password

**Password.Add**

Adds a password to the password manager.

*Parameters:*  
	`site`, the site for which the password is retrieved  
	`new`, the new password that is added  
	`master`, the encryption key and identity verification

*Returns:* `message (string)`

**Password.Retrieve**

Retrieves a password from the password manager.

*Parameters:*  
	`site`, the site for which the password is retrieved  
	`master`, the encryption key and identity verification

*Returns:* `password (string)`

### XBMC

**XBMC.Pause**

Pauses current stream.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Next**

Skips to next song in queue.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Previous**

Skips to previous song in queue.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Shuffle**

Shuffles Now Playing queue.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Mute**

Mutes XBMC.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Unmute**

Unmutes XBMC.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Up**

Moves XBMC selection up.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Down**

Moves XBMC selection down.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Left**

Moves XBMC selection left.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Right**

Moves XBMC selection right.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Select**

Makes XBMC selection.

*Parameters:* `none`

*Returns:* `message (string)`

**XBMC.Volume**

Sets XBMC volume.

*Parameters:* `volume (string)`

*Returns:* `message (string)`

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