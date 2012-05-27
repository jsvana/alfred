#!/usr/bin/env python3
import cmd, shlex, getpass, json, urllib.request

class Alfred(cmd.Cmd, object):

	# TODO: set strings to format documentation
	prompt = "> "
	apikey = ""
	url = "http://psg.mtu.edu:21516/alfred/alfred.rpc"
	api = "0.1"
	key = ""
	intro = "Hello, Sir. How may I help?"
	#doc_header = "What may I help you with today?"
	#misc_header = ""
	#undoc_header = ""
	#ruler = ""
	
	def __init__(self):
		super().__init__()

	def request(self, method, params = {}):
		data = json.dumps({'alfred': self.api, 'key': self.key, 'method': method, 'params': params}).encode('utf-8')
		req = urllib.request.Request(self.url, data)
		req_data = urllib.request.urlopen(req).read().decode('utf-8')
		req_data = json.loads(req_data)
		if 'code' in req_data:
			code = int(req_data['code'])
			if code < 0:
				if 'message' in req_data['data']:
					print(req_data['data']['message'])
				else:
					print(req_data['message'])
			return (code, req_data)

	def do_quit(self, s):
		return True

	def do_exit(self, s):
		return True

	def do_EOF(self, s):
		print()
		return True

	def complete_minecraft(self, text, line, begidx, endidx):
		return [x for x in ['motd', 'players', 'maxplayers'] if x.startswith(text)]

	def do_minecraft(self, s):
		args = shlex.split(s)
		if len(args) == 2:
			params = {'server': args[1]}
			if args[0] == "motd":
				(code, data) = self.request('Minecraft.MOTD', params)
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

	def do_time(self, s):
		(code, data) = self.request('Alfred.Time')

	def do_login(self, s):
		args = shlex.split(s)
		if len(args) != 2:
			print("Please enter a username and password.")
			return

		username = args[0]
		password = args[1]

		(code, data) = self.request('Alfred.Login', {'username': username, 'password': password})

		if 'key' in data['data']:
			key = data['data']['key']
			print("Login successful.")
		else:
			print("Error in logging in.")
		
if __name__ == '__main__':
	Alfred().cmdloop()
