#!/usr/bin/env python3
import cmd, shlex, getpass, json, urllib.request

class Alfred(cmd.Cmd, object):

	# TODO: set strings to format documentation
	prompt = "> "
	apikey = ""
	url = "http://alf.re/d/"
	#url = "http://localhost:21516/alfred/PHPServer/"
	#url = "http://psg.mtu.edu:21516/alfred/PHPServer/"
	#url = "http://alfred.phpfogapp.com/PHPServer/"
	api = "0.1"
	key = ""
	intro = "Hello, Sir. How may I help?"
	doc_header = "What may I help you with today, Sir?"

	debug = False
	#misc_header = ""
	#undoc_header = ""
	ruler = ""

	def __init__(self):
		super().__init__()

	def help_help(self):
		print('help [module]')

	def get_names(self):
		if self.key == "":
			return ['do_login', 'help_login']

		temp = dir(self.__class__)
		temp.remove('do_EOF')
		temp.remove('do_exit')
		temp.remove('do_quit')
		temp.remove('do_login')

		return temp

	def request(self, method, params = {}):
		data = json.dumps({'alfred': self.api, 'key': self.key, 'method': method, 'params': params}).encode('utf-8')
		if self.debug: print(data)
		# TODO: Make the urllib stuff safer
		req = urllib.request.Request(self.url, data)
		req_data = urllib.request.urlopen(req).read().decode('utf-8')
		if self.debug: print("Response: " + req_data)
		req_data = json.loads(req_data)
		if 'code' in req_data:
			code = int(req_data['code'])
			if code < 0:
				if 'message' in req_data['data']:
					print(req_data['data']['message'])
				else:
					print(req_data['message'])
			return (code, req_data)
		return (-255, req_data)

	def do_quit(self, s):
		return True

	def do_exit(self, s):
		return True

	def do_EOF(self, s):
		print()
		return True

	def generic_complete(self, text, data):
		return [x for x in data if x.startswith(text)]

	def complete_minecraft(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['motd', 'players', 'maxplayers'])

	def help_minecraft(self):
		print('minecraft motd|players|maxplayers <url>')

	def do_minecraft(self, s):
		args = shlex.split(s)
		if len(args) == 2:
			params = {'server': args[1]}
			if args[0] == "motd":
				(code, data) = self.request('Minecraft.MOTD', params)
				if code >= 0: print(data['data']['motd'])
			elif args[0] == "players":
				(code, data) = self.request('Minecraft.Players', params)
			elif args[0] == "maxplayers":
				(code, data) = self.request('Minecraft.MaxPlayers', params)
			else:
				print("Unknown Minecraft command.")
		else:
			# TODO: This displays for any MC command, even if it doesn't exist...
			# What? I was lazy
			print("Please specify a server.")

	def complete_bitbucket(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['status', 'following'])

	def help_bitbucket(self):
		print("bitbucket status")

	def do_bitbucket(self, s):
		args = shlex.split(s)
		if len(args) >= 1:
			if args[0] == "status":
				(code, data) = self.request('Net.Bitbucket.Status')
				if code >= 0: print("Status, as of " + data['data']['time'] + ": " + data['data']['description'])
			elif args[0] == "following":
				if len(args) != 2:
					print("Please specify a user.")
				else:
					(code, data) = self.request('Net.Bitbucket.Followers', {'user': args[1]})
					if code >= 0:
						print(", ".join(map(lambda u: u['username'], data['data']['followers'])))
			else:
				print("Unknown Bitbucket command.")
		else:
			print("Please specify a Bitbucket command.")

	def complete_github(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['status'])

	def help_github(self):
		print("github status")

	def do_github(self, s):
		args = shlex.split(s)
		if len(args) == 1:
			if args[0] == "status":
				(code, data) = self.request('Net.Github.Status')
				if code >= 0: print("Status, as of " + data['data']['time'] + ": " + data['data']['description'])
			else:
				print("Unknown Github command.")
		else:
			print("Please specify a Github command.")

	def complete_heroku(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['status'])

	def help_heroku(self):
		print("heroku status")

	def do_heroku(self, s):
		args = shlex.split(s)
		if len(args) == 1:
			if args[0] == "status":
				(code, data) = self.request('Net.Heroku.Status')
				if code >= 0: print("Status: " + data['data']['status']['Production'])
			else:
				print("Unknown Heroku command.")
		else:
			print("Please specify a Heroku command.")

	def help_time(self):
		print('time')

	def do_time(self, s):
		(code, data) = self.request('Alfred.Time')
		if code >= 0: print(data['data']['time'])
	
	def help_directions(self):
		print('directions <from> to <to>')
	
	def  do_directions(self, s):
		args =  shlex.split(s)
		if len(args) > 0:
			if 'to' in args:
				index = args.index('to')
				directionsFrom = " ".join(args[0:index])
				directionsTo = " ".join(args[(index + 1):len(args)])
				(code, data) = self.request('Location.Directions', {'from': directionsFrom, 'to': directionsTo})
				if code >= 0: print("\n".join(map(lambda m: m['narrative'], data['data']['directions'])))
			else:
				print("Invalid directions query.")
		else:
			print("Unknown directions command.")

	def help_logout(self):
		print('logout')

	def do_logout(self, s):
		(code, data) = self.request('Alfred.Logout')
		if code >= 0: print(data['data']['message'])

	def help_shorten(self):
		print("shorten <url>")

	def do_shorten(self, s):
		args = shlex.split(s)
		if len(args) == 1:
			(code, data) = self.request('Net.Shorten', {'url': args[0]})
			if code >= 0: print(data['data']['url'])
		else:
			print("Please specify a URL.")

	def complete_password(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['retrieve', 'add'])

	def help_password(self):
		print('password retrieve <site> <username> <master password>')
		print('password add <site> <username> <pass> <master password>')

	def do_password(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			if args[0] == "retrieve":
				if len(args) < 4:
					print("Please enter a site, username, and your master password.")
					return
				(code, data) = self.request('Password.Retrieve', {'site': args[1], 'username': args[2], 'master': args[3]})
				if code >= 0:
					print(data['data']['password'])
			elif args[0] == "add":
				if len(args) < 5:
					print("Please enter a site, new password, and your master password.")
					return
				(code, data) = self.request('Password.Add', {'site': args[1], 'username': args[2], 'new': args[3], 'master': args[4]})
				if code >= 0:
					print(data['data']['message'])
			else:
				print("Unknown password command.")
		else:
			print("Unknown password command.")

	def complete_twitter(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['last', 'tweet', 'tweets', 'startauth', 'completeauth'])

	def help_twitter(self):
		print('twitter startauth|completeauth|tweet|tweets|last')

	def do_twitter(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			if args[0] == "last":
				if len(args) < 2:
					print("Please specify a user.")
					return
				(code, data) = self.request('Net.Twitter.LastTweet', {'user': args[1]})
				if code >= 0:
					print(data['data']['tweet'])
			elif args[0] == "tweets":
				if len(args) < 2:
					print("Please specify a user.")
					return
				(code, data) = self.request('Net.Twitter.Tweets', {'user': args[1]})
				if code >= 0:
					print(", ".join(map(lambda t: "\"" + t['text'] + "\"", data['data']['tweets'])))
			elif args[0] == "startauth":
				(code, data) = self.request('Net.Twitter.StartAuth')
				if code >= 0: print(data['data']['url'])
			elif args[0] == "completeauth":
				if len(args) < 2:
					print("Please specify a PIN.")
					return
				(code, data) = self.request('Net.Twitter.CompleteAuth', {'verifier': args[1]})
				if code >= 0: print(data['data']['message'])
			elif args[0] == "tweet":
				if len(args) < 2:
					print("Please provide a tweet.")
					return
				(code, data) = self.request('Net.Twitter.Tweet', {'tweet': " ".join(args[1:])})
				if code >= 0: print(data['data']['message'])
			else:
				print("Unknown Twitter command.")
		else:
			print("Please specify a Twitter command.")
	
	def complete_fatsecret(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['food '])
	
	def help_fatsecret(self):
		print('fatsecret food <food>')
	
	def do_fatsecret(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			if args[0] == "food":
				if len(args) < 2:
					print("Please specify a food.")
					return
				(code, data) = self.request('Net.FatSecret.Food', {'food': " ".join(args[1:])})
				if code >= 0:
					if data['data']['foods'] == None:
						print("No results found.")
					else:
						print("First result: " + data['data']['foods'][0]['food_name'] + " (" + data['data']['foods'][0]['food_description'] + ")")
			else:
				print("Unknown FatSecret command.")
		else:
			print("Please specify a FatSecret command.")
	
	def complete_tmdb(self, text, line, begidx, endidx):
		return self.generic_complete(text, ['movie '])
	
	def help_tmdb(self):
		print('tmdb movie <title>')
	
	def do_tmdb(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			if args[0] == "movie":
				if len(args) < 2:
					print("Please specify a movie.")
					return
				(code, data) = self.request('Net.TMDB.Movie', {'title': " ".join(args[1:])})
				if code >= 0:
					if data['data']['total_results'] > 0:
						print("First result: " + data['data']['first_result']['title'] + ", released " + data['data']['first_result']['release_date'] + " and rated " + str(data['data']['first_result']['vote_average']) + " out of 10.")
					else:
						print("No results found.")
			else:
				print("Unknown TMDB command.")
		else:
			print("Please specify a TMDB command.")
	
	def help_currency(self):
		print('currency <amount> <from> in <to>')
	
	def do_currency(self, s):
		args = shlex.split(s)
		if len(args) == 4:
			(code, data) = self.request('Location.Currency', {'amount': args[0], 'from': args[1], 'to': args[3]})
			if code >= 0:
				print(str(data['data']['amount']) + ' ' + args[3])
		else:
			print('Incorrect arguments.')

	def help_weather(self):
		print('weather <zip>')

	def do_weather(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			(code, data) = self.request('Location.Weather', {'zip': args[0]})
			if code >= 0: print("Weather for " + data['data']['location'] + ": " + data['data']['temp'] + "\u00b0C, " + data['data']['text'])
		else:
			print("Please specify a location.")

	def help_iplookup(self):
		print('iplookup <ip>')
	
	def do_iplookup(self, s):
		args  = shlex.split(s)
		if len(args) > 0:
			(code, data) = self.request('Location.IPLookup', {'ip': args[0]})
			if code >= 0: print("Location: " + data['data']['cityName'] + ", " + data['data']['regionName'] + ", " + data['data']['countryName'])
		else:
			print("Please specify an IP address.")
	
	def help_ip(self):
		print('ip')
	
	def do_ip(self, s):
		(code, data) = self.request('Net.ClientIP')
		if code >= 0: print("My IP: " + data['data']['ip'])

	def help_zip(self):
		print('zip <city>')

	def do_zip(self, s):
		(code, data) = self.request('Location.Zip', {'city': s})
		if code >= 0: print("Zip Code: " + data['data']['zip'])

	def help_areacode(self):
		print('areacode <city|zip>')

	def do_areacode(self, s):
		(code, data) = self.request('Location.AreaCode', {'city': s})
		if code >= 0: print("Area Code: " + data['data']['areacode'])

	def help_airport(self):
		print('airport <city|zip>')

	def do_airport(self, s):
		(code, data) = self.request('Location.NearestAirport', {'city': s})
		if code >= 0: print("Nearest airport: " + data['data']['airport'])

	def help_ping(self):
		print('ping <host>')

	def do_ping(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			(code, data) = self.request('Net.Ping', {'host': args[0]})
			if code >= 0:
				print(data['data']['response'])
			else:
				print("Error pinging host.")
		else:
			print("Please specify a host.")

	def help_dns(self):
		print('ping <host>')

	def do_dns(self, s):
		args = shlex.split(s)
		if len(args) > 0:
			(code, data) = self.request('Net.DNS', {'host': args[0]})
			if code >= 0:
				print(data['data']['response'])
			else:
				print("Error in host lookup.")
		else:
			print("Please specify a host.")

	def help_login(self):
		print('login <username> <password>')

	def do_login(self, s):
		args = shlex.split(s)
		if len(args) != 2:
			print("Please enter a username and password.")
			return

		username = args[0]
		password = args[1]

		(code, data) = self.request('Alfred.Login', {'username': username, 'password': password})

		if 'key' in data['data']:
			self.key = data['data']['key']
			print("Login successful.")
		else:
			print("Error in logging in.")

if __name__ == '__main__':
	Alfred().cmdloop()
