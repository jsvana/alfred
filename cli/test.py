#!/usr/bin/env python3
import cmd, shlex, getpass, json, urllib.request

class Alfred(cmd.Cmd, object):

	# TODO: set strings to format documentation
	prompt = "> "
	apikey = ""
	url = "http://psg.mtu.edu:21516/alfred/PHPServer/"
	api = "0.1"
	key = ""
	intro = "Hello, Sir. How may I help?"
	doc_header = "What may I help you with today, Sir?"
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
		print(data)
		# TODO: Make the urllib stuff safer
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
		return self.generic_complete(text, ['status'])

	def help_bitbucket(self):
		print("bitbucket status")

	def do_bitbucket(self, s):
		args = shlex.split(s)
		if len(args) == 1:
			if args[0] == "status":
				(code, data) = self.request('Net.Bitbucket.Status')
				if code >= 0: print("Status, as of " + data['data']['time'] + ": " + data['data']['description'])
			else:
				print("Unknown Bitbucket command.")
		else:
			print("Please specify a Bitbucket command.")

	def help_time(self):
		print('time')

	def do_time(self, s):
		(code, data) = self.request('Alfred.Time')

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
