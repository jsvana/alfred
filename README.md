### Syntax

Commands are sent to Alfred in the following format:

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
		"method": "App.Login",
		"params": {
			"username": "linus",
			"password": "hunter2"
		}
	}

and the server will send you back a reply in the form

	{
		"result": {
			"key": "fc2baa1a20b4d5190b122b383d7449fd"
		}
	}

if you successfully logged in, or in the form

	{
		"error": {
			"code": -2,
			"message": "Incorrect username or password."
		}
	}

if there was an error.

### Commands

#### Alfred

*Alfred.Login*

Initiates session with the server.

**Parameters:**  
	`username`, the username for the user  
	`password`, the password for the user

**Returns:** `key (string)`

*Alfred.Time*

Gets the server time.

**Parameters:** `none`

**Returns:** `time (string, formatted YYYY-mm-dd hh:mm:ss GMT-hh:mm)`

#### Location

*Location.Weather*

Fetches current weather for a given zip code.

**Parameters:** `zip`, the zip code for the area

**Returns:**  
	`location`, the city and state for the conditions  
	`text`, a description of the conditions  
	`temp`, the current temperature (in Celcius)  
	`date`, the date of the conditions  

#### Network

*Network.Ping*

Pings a host from the server.

**Parameters:** `host`, the host to ping

**Returns:** `output (string)`

*Network.DNS*

Looks up a host from the server.

**Parameters:** `host`, the host to lookup

**Returns:** `output (string)`

#### Password

*Password.Add*

Adds a password to the password manager.

**Parameters:**  
	`site`, the site for which the password is retrieved  
	`new`, the new password that is added  
	`master`, the encryption key and identity verification

**Returns:** nothing

*Password.Retrieve*

Retrieves a password from the password manager.

**Parameters:**  
	`site`, the site for which the password is retrieved  
	`master`, the encryption key and identity verification

**Returns:** `password (string)`

#### XBMC

*XBMC.GetPlayer*

Gets currently playing audio player.

**Parameters:** `none`

**Returns:** `playerID (string)`

*XBMC.Pause*

Pauses current stream.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Next*

Skips to next song in queue.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Previous*

Skips to previous song in queue.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Shuffle*

Shuffles Now Playing queue.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Mute*

Mutes XBMC.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Unmute*

Unmutes XBMC.

**Parameters:** `none`

**Returns:** `none`

*XBMC.Volume*

Sets XBMC volume.

**Parameters:** `volume (string)`

**Returns:** `none`

### Responses

Responses take the form

	{
		"code": -1,
		"message": "Malformed command.",
		"data": { }
	}

If the code is less than zero, the response is an error.  Otherwise, the response is a successful result.

#### Results



#### Errors

`-1` - `Malformed command`: the command JSON was not formatted correctly  
`-2` - `Unknown command`: the user specified an unknown method  
`-3` - `Not authenticated`: the user has not yet authenticated  
`-4` - `Incorrect parameters`: parameter(s) are invalid or missing  
`-5` - `Method failed`: method did not execute successfully  