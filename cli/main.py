#!/usr/bin/env python3

import urllib.request
import json

apiKey = ""

ALFRED_HOST = "psg.mtu.edu"
ALFRED_PORT = "21516"
ALFRED_LOCATION = "/alfred/PHPServer/"

def processCommand(cmd):
	if len(cmd) == 0 or cmd == "quit":
		return

	words = cmd.split(" ")

	postData = ""
	retCommand = ""

	if words[0] == "login":
		global apiKey

		if len(words) < 3:
			print("Please enter a username and password.")
			return

		username = words[1]
		password = words[2]

		postData += "{\"alfred\":\"0.1\",\"key\":\"\",\"method\":\"Alfred.Login\",\"params\":{\"username\":\"" + username + "\",\"password\":\"" + password + "\"}}"
		retCommand = "Alfred.Login"

	elif words[0] == "time":
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Alfred.Time\",\"params\":{ }}"
		retCommand = "Alfred.Time"
	
	elif words[0] == "password":
		if len(words) < 2:
			print("Please enter a password command.")
			return

		passwordCommand = words[1]

		if passwordCommand == "retrieve":
			if len(words) < 5:
				print("Please enter a site, username, and your master password.")
				return

			site = words[2]
			usernameRetrieve = words[3]
			masterPassword = words[4]

			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Password.Retrieve\",\"params\":{\"site\":\"" + site + "\",\"username\":\"" + usernameRetrieve + "\",\"master\":\"" + masterPassword + "\"}}"
			retCommand = "Password.Retrieve"
		elif passwordCommand == "add":
			if len(words) < 6:
				print("Please enter a site, new password, and your master password.")
				return
			siteAdd = words[2]
			usernameAdd = words[3]
			newPassAdd = words[4]
			masterAdd = words[5]

			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Password.Add\",\"params\":{\"site\":\"" + siteAdd + "\",\"username\":\"" + usernameAdd + "\",\"new\":\"" + newPassAdd + "\",\"master\":\"" + masterAdd + "\"}}"
			retCommand = "Password.Add"
		else:
			print("Unknown password command.")
			return

	elif words[0] == "minecraft":
		if len(words) < 2:
			print("Please specify a command.")
			return

		minecraftCommand = words[1]

		if minecraftCommand == "motd":
			if len(words) < 3:
				print("Please specify a server.")
				return

			minecraftMOTDServer = words[2]
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.MOTD\",\"params\":{\"server\":\"" + minecraftMOTDServer + "\"}}"
			retCommand = "Minecraft.MOTD"

		elif minecraftCommand == "players":
			if len(words) < 3:
				print("Please specify a server.")
				return

			minecraftCountServer = words[2]
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.Players\",\"params\":{\"server\":\"" + minecraftCountServer + "\"}}"
			retCommand = "Minecraft.Players"

		elif minecraftCommand == "maxplayers":
			if len(words) < 3:
				print("Please specify a server.")
				return
			
			minecraftMaxPlayersServer = words[2]
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Minecraft.MaxPlayers\",\"params\":{\"server\":\"" + minecraftMaxPlayersServer + "\"}}"
			retCommand = "Minecraft.MaxPlayers"
		
		else:
			print("Unknown Minecraft command.")
			return

	elif words[0] == "ping":
		if len(words) < 2:
			print("Please enter a host.")
			return

		host = words[1]
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Net.Ping\",\"params\":{\"host\":\"" + host + "\"}}"
		retCommand = "Net.Ping"

	elif words[0] == "dns":
		if len(words) < 2:
			print("Please enter a host.")
			return

		dnsHost = words[1]
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Net.DNS\",\"params\":{\"host\":\"" + dnsHost + "\"}}"
		retCommand = "Net.DNS"

	elif words[0] == "shorten":
		if len(words) < 2:
			print("Please enter a url.")
			return

		shortenURL = words[1]
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Net.Shorten\",\"params\":{\"url\":\"" + shortenURL + "\"}}"
		retCommand = "Net.Shorten"

	elif words[0] == "lmgtfy":
		if len(words) < 2:
			print("Please enter a string to LMGTFY.")
			return

		text = " ".join(words[1:])
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Net.LMGTFY\",\"params\":{\"text\":\"" + text + "\"}}"
		retCommand = "Net.LMGTFY"

	elif words[0] == "twitter":
		if len(words) < 2:
			print("Please enter a Twitter command.")
			return

		twitterCommand = words[1]

		if twitterCommand == "last":
			if len(words) < 3:
				print("Please specify a user.")
				return

			user = words[2]
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Net.Twitter.LastTweet\",\"params\":{\"user\":\"" + user + "\"}}"
			retCommand = "Net.Twitter.LastTweet"
		else:
			print("Unknown Twitter command.")
			return

	elif words[0] == "weather":
		if len(words) < 2:
			print("Please enter a zip code.")
			return

		zip = words[1]
		postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"Location.Weather\",\"params\":{\"zip\":\"" + zip + "\"}}"
		retCommand = "Location.Weather"

	elif words[0] == "xbmc":
		if len(words) < 2:
			print("Please specify an XBMC command.")
			return

		xbmcCommand = words[1]

		if xbmcCommand == "pause":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Pause\",\"params\":{}}"
			retCommand = "XBMC.Pause"
		elif xbmcCommand == "mute":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Mute\",\"params\":{}}"
			retCommand = "XBMC.Mute"
		elif xbmcCommand == "unmute":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Unmute\",\"params\":{}}"
			retCommand = "XBMC.Unmute"
		elif xbmcCommand == "next" or xbmcCommand == "skip":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Next\",\"params\":{}}"
			retCommand = "XBMC.Next"
		elif xbmcCommand == "previous":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Previous\",\"params\":{}}"
			retCommand = "XBMC.Previous"
		elif xbmcCommand == "volume":
			if len(words) < 3:
				print("Please specify a volume.")
				return
			try:
				volume = int(words[2])
			except ValueError:
				print("Please specify a valid volume.")
				return

			if volume < 0 or volume > 100:
				print("Volume must be between 0 and 100.")
				return

			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Volume\",\"params\":{\"volume\":" + words[2] + "}}"
			retCommand = "XBMC.Volume"
		elif xbmcCommand == "shuffle":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Shuffle\",\"params\":{}}"
			retCommand = "XBMC.Shuffle"
		elif xbmcCommand == "up":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Up\",\"params\":{}}"
			retCommand = "XBMC.Up"
		elif xbmcCommand == "down":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Down\",\"params\":{}}"
			retCommand = "XBMC.Down"
		elif xbmcCommand == "left":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Left\",\"params\":{}}"
			retCommand = "XBMC.Left"
		elif xbmcCommand == "right":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Right\",\"params\":{}}"
			retCommand = "XBMC.Right"
		elif xbmcCommand == "select":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.Select\",\"params\":{}}"
			retCommand = "XBMC.Select"
		elif xbmcCommand == "player":
			postData += "{\"alfred\":\"0.1\",\"key\":\"" + apiKey + "\",\"method\":\"XBMC.GetPlayer\",\"params\":{}}"
			retCommand = "XBMC.GetPlayer"
		else:
			print("Unknown XBMC command.")
			return
	else:
		print("Unknown command.")

	url = "http://psg.mtu.edu:21516/alfred/alfred.rpc"
	url = "http://" + ALFRED_HOST + ":" + ALFRED_PORT + ALFRED_LOCATION
	postData = postData.encode('utf-8')
	req = urllib.request.Request(url, postData)
	responseData = urllib.request.urlopen(req)
	text = responseData.read()
	print(text)
	
	retJson = json.loads(text.decode('utf-8'))

	if 'code' in retJson:
		try:
			code = int(retJson['code'])
		except ValueError:
			print("Internal server error.")

		if code < 0:
			if 'message' in retJson['data']:
				print(retJson['data']['message'])
			else:
				print(retJson['message'])
		else:
			retData = retJson['data']

			# Alfred response
			if retCommand == "Alfred.Login":
				if 'key' in retData:
					apiKey = retJson['data']['key']
					print("Login successful.")
				else:
					print("Error in logging in.")
			elif retCommand == "Alfred.Time":
				if 'time' in retData:
					print("Alfred's time: " + retData['time'])
				else:
					print("Error getting time.")

			# Minecraft response
			elif retCommand == "Minecraft.MOTD":
				if 'motd' in retData:
					print(retData["motd"])
				else:
					print("Error in retrieving server MOTD.")
			elif retCommand == "Minecraft.Players":
				if 'players' in retData:
					print(retData['players'])
				else:
					print("Error in retrieving server player count.")
			elif retCommand == "Minecraft.MaxPlayers":
				if 'maxPlayers' in retData:
					print(retData['maxPlayers'])
				else:
					print("Error in retrieving server max player count.")

			# Password response
			elif retCommand == "Password.Retrieve":
				if 'password' in retData:
					print(retData['password'])
				else:
					print("Error in retrieving password.")
			elif retCommand == "Password.Add":
				if 'message' in retData:
					print(retData['message'])
				else:
					print("Error in adding password.")

			# Net response
			elif retCommand == "Net.DNS":
				if 'response' in retData:
					print(retData['response'])
				else:
					print("Error in host lookup.")
			elif retCommand == "Net.Ping":
				if 'response' in retData:
					print(retData['response'])
				else:
					print("Error pinging host.")
			elif retCommand == "Net.Shorten":
				if 'url' in retData:
					print(retData['url'])
				else:
					print("Error shortening URL.")
			elif retCommand == "Net.LMGTFY":
				if 'url' in retData:
					print(retData['url'])
				else:
					print("Error getting LMGTFY URL.")
			elif retCommand == "Net.Twitter.LastTweet":
				if 'tweet' in retData:
					print(retData['tweet'])
				else:
					print("Error getting user's last tweet.")

			# Location response
			elif retCommand == "Location.Weather":
				if 'location' in retData and 'text' in retData and 'temp' in retData:
					print("Weather for " + retData['location'] + ": " + retData['temp'] + "\u00b0C, " + retData['text'])
				else:
					print("Error retrieving weather.")

			# XBMC response
			elif retCommand == "XBMC.GetPlayer":
				if 'playerids' in retData:
					players = retData['playerids']
					if len(players) > 0:
						playerID = players[0]['playerid']
						print("PlayerID: " + playerID)
				else:
					print("Error in retrieving playerID.")
			elif retCommand == "XBMC.Pause" or retCommand == "XBMC.Mute" or retCommand == "XBMC.Unmute" or retCommand == "XBMC.Next" or retCommand == "XBMC.Previous" or retCommand == "XBMC.Volume" or retCommand == "XBMC.Shuffle" or retCommand == "XBMC.Up" or retCommand == "XBMC.Down" or retCommand == "XBMC.Left" or retCommand == "XBMC.Right" or retCommand == "XBMC.Select":
				if 'message' in retData:
					print(retData['message'])
				else:
					print("Error in sending XBMC command.")

			# Default response
			else:
				print(retJson['message'])
	else:
		print("Internal server error.")

print("Hello, Sir.  How may I help?")

command = input("> ")

processCommand(command)

while command != "quit":
	command = input("> ")

	processCommand(command)