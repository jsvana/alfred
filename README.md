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

#### App

*App.Login*

Initiates session with the server.

**Parameters:** username, password

**Returns:** API Key (string)

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

#### Weather

*Weather.Current*

Fetches current weather for a given zip code.

**Parameters:** `zip (string)`

**Returns:**  
	`location`, the city and state for the conditions
	`text`, a description of the conditions
	`temp`, the current temperature (in Celcius)
	`date`, the date of the conditions