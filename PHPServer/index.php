<?php
	session_start();

	include("config.php");

	$data = json_decode(file_get_contents("php://input"));

	if(!isset($data) || !isset($data->alfred) || !isset($data->key) || !isset($data->method) || !isset($data->params)) {
		echo alfred_error(-1);
		return;
	}

	$method = $data->method;
	$params = $data->params;

	mysql_connect($MYSQL_HOSTNAME, $MYSQL_USERNAME, $MYSQL_PASSWORD);
	mysql_select_db($MYSQL_DATABASE);

	$ret = "";

	switch($method) {
		/* Alfred */
		case "Alfred.Login":
			if(($message = validate_parameters($params, array("username", "password"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$username = $params->username;
				$password = $params->password;

				if(mysql_num_rows(mysql_query("SELECT `username` FROM `users` WHERE `username`='" . mysql_real_escape_string($username) . "' AND `password`='" . md5($password) . "';")) > 0) {
					$key = md5($username . $password . time());
					mysql_query("INSERT INTO `sessions` (api_key, expiration) VALUES ('" . $key . "', DATE_ADD(NOW(), INTERVAL 1 HOUR));");

					$ret = alfred_result(0, array("key" => $key));
				} else {
					$ret = alfred_error(-5, array("message" => "Incorrect username or password."));
				}
			}
			break;
		case "Alfred.Time":
			$ret = alfred_result(0, array("time" => date("Y-m-d H:i:s \G\M\TP")));

			break;

		/* Location */
		case "Location.Weather":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("zip"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$weather_feed = file_get_contents("http://weather.yahooapis.com/forecastrss?p=" . $params->zip . "&u=c");
				$weather = simplexml_load_string($weather_feed);
				if(!$weather) die('weather failed');
				$copyright = $weather->channel->copyright;

				$channel_yweather = $weather->channel->children("http://xml.weather.yahoo.com/ns/rss/1.0");

				foreach($channel_yweather as $x => $channel_item) 
					foreach($channel_item->attributes() as $k => $attr) 
						$yw_channel[$x][$k] = $attr;

				$item_yweather = $weather->channel->item->children("http://xml.weather.yahoo.com/ns/rss/1.0");

				foreach($item_yweather as $x => $yw_item) {
					foreach($yw_item->attributes() as $k => $attr) {
						if($k == 'day') $day = $attr;
						if($x == 'forecast') { $yw_forecast[$x][$day . ''][$k] = $attr;	} 
						else { $yw_forecast[$x][$k] = $attr; }
					}
				}

				$ret = alfred_result(0, array("location" => $yw_channel['location']['city'] . ", " . $yw_channel['location']['region'], "text" => $yw_forecast['condition']['text'], "temp" => $yw_forecast['condition']['temp'], "date" => $yw_forecast['condition']['date']));
			}
			break;

		/* Minecraft */
		case "Minecraft.MOTD":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("server"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$serverArr = explode(":", $params->server);

				if(count($serverArr) === 1) {
					$result = minecraft_ping($serverArr[0]);
				} else {
					$result = minecraft_ping($serverArr[1]);
				}

				$ret = alfred_result(0, array("motd" => $result['motd']));
			}
			break;
		case "Minecraft.Players":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("server"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$serverArr = explode(":", $params->server);

				if(count($serverArr) === 1) {
					$result = minecraft_ping($serverArr[0]);
				} else {
					$result = minecraft_ping($serverArr[1]);
				}

				$ret = alfred_result(0, array("players" => $result['players']));
			}
			break;
		case "Minecraft.MaxPlayers":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("server"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$serverArr = explode(":", $params->server);

				if(count($serverArr) === 1) {
					$result = minecraft_ping($serverArr[0]);
				} else {
					$result = minecraft_ping($serverArr[1]);
				}

				$ret = alfred_result(0, array("maxPlayers" => $result['maxPlayers']));
			}
			break;

		/* Network */
		case "Network.Ping":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("host"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$output = shell_exec("ping -c 1 " . $params->host);

				$ret = alfred_result(0, array("reponse" => $output));
			}
			break;
		case "Network.DNS":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("host"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$output = shell_exec("dig " . $params->host);

				$arr = explode("\n", $output);

				$res = array_search(";; ANSWER SECTION:", $arr);

				if($res !== false) {
					$line = $arr[$res + 1];
					$tokens = explode("\t", $line);

					$ret = alfred_result(0, array("response" => $tokens[0] . " " . $tokens[count(tokens) - 1]));
				} else {
					$ret = alfred_result(0, array("response" => "Unknown host."));
				}
			}
			break;

		/* Password */
		case "Password.Retrieve":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("master", "site", "username"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$result = mysql_query("SELECT `password` FROM `passwords` WHERE `site`='" . mysql_real_escape_string($params->site) . "' AND `username`='" . mysql_real_escape_string($params->username) . "';");
				if(mysql_num_rows($result) === 0) {
					$ret = alfred_error(-3, array("message" => "Site not in database."));
				} else {
					$row = mysql_fetch_assoc($result);
					$pass = decrypt($row['password'], $data->{'params'}->{'master'});
					$ret = alfred_result(0, array("password" => $pass));
				}
			}
			break;
		case "Password.Add":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("master", "site", "new"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				$result = mysql_query("SELECT `password` FROM `passwords` WHERE `site`='" . mysql_real_escape_string($params->site) . "' AND `username`='" . mysql_real_escape_string($params->username) . "';");
				if(mysql_num_rows($result) === 0) {
					mysql_query("INSERT INTO `passwords` (site, username, password) VALUES ('" . mysql_real_escape_string($params->site) . "', '" . mysql_real_escape_string($params->username) . "', '" . encrypt($params->new, $data->{'params'}->{'master'}) . "');");
					$ret = alfred_result(0, array("message" => "Password inserted successfully."));
				} else {
					$ret = alfred_error(-3, array("message" => "Site not in database."));
				}
			}
			break;

		/* XBMC */
		case "XBMC.Pause":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.PlayPause\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Mute":
		case "XBMC.Unmute":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetMute\", \"params\": { \"mute\": " . ($method === "XBMC.Mute" ? "true" : "false") . " }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Next":
		case "XBMC.Previous":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.Go" . ($method === "XBMC.Next" ? "Next" : "Previous") . "\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Volume":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else if(($message = validate_parameters($params, array("volume"))) !== "") {
				$ret = alfred_error(-4, array("message" => $message));
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetVolume\", \"params\": { \"volume\": " . $params->volume . " }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Shuffle":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.Shuffle\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Up":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Input.Up\", \"params\": { }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Down":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Input.Down\", \"params\": { }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Left":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Input.Left\", \"params\": { }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Right":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Input.Right\", \"params\": { }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.Select":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Input.Select\", \"params\": { }, \"id\": 1}");

				$ret = alfred_result(0, array("message" => "Command sent."));
			}
			break;
		case "XBMC.GetPlayer":
			if(!isset($data->key) || $data->key === "" || !session_authenticated($data->key)) {
				$ret = alfred_error(-3);
			} else {
				$result = xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"params\": { }, \"id\": 1}");
				
				$resultJSON = json_decode($result);

				$ret = alfred_result(0, array("message" => "Command sent.", "playerids" => json_encode($resultJSON->result)));
			}
			break;

		/* Unknown command */
		default:
			$ret = alfred_error(-2);
			break;
	}

	echo $ret;

	function validate_parameters($params, $valid) {
		if(count($params) > count(valid)) {
			return "Too many parameters.";
		}

		$missing = array();
		$empty = array();

		foreach($valid as $key) {
			if(!isset($params->{$key})) {
				$missing[$key] = true;
			} else if($params->{$key} === "") {
				$empty[$key] = true;
			}
		}

		$message = "";

		if(count($missing) !== 0) {
			$message = "Missing parameter" . (count($missing) === 1 ? "" : "s") . " '" . join("', '", array_keys($missing)) . "'";
		}

		if(count($empty) !== 0) {
			if(count($message) !== 0) {
				$message .= ", and parameter" . (count($empty) === 1 ? "" : "s") . " '";
			} else {
				$message = "Parameter" . (count($empty) === 1 ? "" : "s") . " '";
			}

			$message .= join("', '", array_keys($empty)) . "' cannot be empty";
		}

		if($message !== "") {
			$message .= ".";
		}

		return $message;
	}

	function alfred_result($code, $data = null) {
		$json = "";

		switch($code) {
			default:
			case 0:
				$json = "{\"code\":0,\"message\":\"Method success.\",\"data\":{";
				if(isset($data)) {
					$m = array();
					foreach($data as $key => $datum) {
						$m[] = "\"" . $key . "\":\"" . $datum . "\"";
					}

					$json .= join(",", $m);
				}

				$json .= "}}";

				break;
		}

		return $json;
	}

	function alfred_error($code, $data = null) {
		$json = "";

		switch($code) {
			default:
			case -1:
				$json = "{\"code\":-1,\"message\":\"Malformed command.\",\"data\":{}}";
				break;
			case -2:
				$json = "{\"code\":-2,\"message\":\"Unknown command.\",\"data\":{}}";
				break;
			case -3:
				$json = "{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}";
				break;
			case -4:
				$json = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{";

				if(isset($data['message'])) {
					$json .= "\"message\":\"" . $data['message'] . "\"";
				}

				$json .= "}}";
				break;
			case -5:
				$json = "{\"code\":-5,\"message\":\"Method failed.\",\"data\":{";

				if(isset($data['message'])) {
					$json .= "\"message\":\"" . $data['message'] . "\"";
				}

				$json .= "}}";
				break;
		}

		return $json;
	}

	function minecraft_ping($host, $port = 25565, $timeout = 30) {
		//Set up our socket
		$fp = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$fp) return false;

		//Send 0xFE: Server list ping
		fwrite($fp, "\xFE");

		//Read as much data as we can (max packet size: 241 bytes)
		$d = fread($fp, 256);

		//Check we've got a 0xFF Disconnect
		if ($d[0] != "\xFF") return false;

		//Remove the packet ident (0xFF) and the short containing the length of the string
		$d = substr($d, 3);

		//Decode UCS-2 string
		$d = mb_convert_encoding($d, 'auto', 'UCS-2');

		//Split into array
		$d = explode("\xA7", $d);

		//Return an associative array of values
		return array(
			'motd'        =>        $d[0],
			'players'     => intval($d[1]),
			'maxPlayers' => intval($d[2]));
	}

	function xbmc_request($data) {
		global $XBMC_USERNAME;
		global $XBMC_PASSWORD;
		global $XBMC_HOST;
		global $XBMC_PORT;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		return curl_exec($ch);
	}

	function encrypt($val, $key) {
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $val, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

	function decrypt($crypt, $key) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($crypt), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}

	function session_authenticated($key) {
		$result = mysql_query("UPDATE `sessions` SET `expiration`=DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE `api_key`='" . mysql_real_escape_string($key) . "' AND `expiration`>NOW();");
		return mysql_affected_rows() > 0;
	}
?>
