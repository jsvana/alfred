<?php
	session_start();

	include("config.php");

	$data = json_decode($_POST['json']);

	if(!isset($data) || !isset($data->alfred) || !isset($data->key) || !isset($data->method) || !isset($data->params)) {
		echo "{\"code\":-1,\"message\":\"Malformed command.\",\"data\":{}}";
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
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$username = $data->params->username;
				$password = $data->params->password;

				if(mysql_num_rows(mysql_query("SELECT `username` FROM `users` WHERE `username`='" . mysql_real_escape_string($username) . "' AND `password`='" . md5($password) . "';")) > 0) {
					$key = md5($username . $password . time());
					mysql_query("INSERT INTO `sessions` (api_key, expiration) VALUES ('" . $key . "', DATE_ADD(NOW(), INTERVAL 1 HOUR));");

					$ret = "{\"result\":{\"key\":\"" . $key . "\"}}";
				} else {
					$ret = "{\"error\":{\"code\":-2,\"message\":\"Incorrect username or password.\"}}";
				}
			}
			break;
		case "Alfred.Time":
			$ret = "{\"result\":{\"message\":\"Time retrieved.\",\"data\":\"" . date("Y-m-d H:i:s \G\M\TP") . "\"}}";

			break;

		/* Location */
		case "Location.Weather":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("zip"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$weather_feed = file_get_contents("http://weather.yahooapis.com/forecastrss?p=" . $data->{'params'}->{'zip'} . "&u=c");
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

				$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":{\"location\":\"" . $yw_channel['location']['city'] . ", " . $yw_channel['location']['region'] . "\",\"text\":\"" . $yw_forecast['condition']['text'] . "\",\"temp\":\"" . $yw_forecast['condition']['temp'] . "\",\"date\":\"" . $yw_forecast['condition']['date'] . "\"}}}";
			}
			break;

		/* Network */
		case "Network.Ping":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("host"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$output = shell_exec("ping -c 1 " . $data->{'params'}->{'host'});

				$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":{\"result\":\"" . $output . "\"}}}";
			}
			break;
		case "Network.DNS":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("host"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$output = shell_exec("dig " . $data->{'params'}->{'host'});

				$arr = explode("\n", $output);

				$res = array_search(";; ANSWER SECTION:", $arr);

				if($res !== false) {
					$line = $arr[$res + 1];
					$tokens = explode("\t", $line);

					$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":{\"result\":\"" . $tokens[0] . " " . $tokens[count($tokens) - 1] . "\"}}}";
				} else {
					$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":{\"result\":\"Unknown host.\"}}}";
				}
			}
			break;

		/* Password */
		case "Password.Retrieve":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("master", "site"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$result = mysql_query("SELECT `password` FROM `passwords` WHERE `site`='" . mysql_real_escape_string($data->{'params'}->{'site'}) . "';");
				if(mysql_num_rows($result) === 0) {
					$ret = "{\"error\":{\"code\":-5,\"message\":\"Site not in database.\",\"data\":{}}}";
				} else {
					$row = mysql_fetch_assoc($result);
					$pass = decrypt($row['password'], $data->{'params'}->{'master'});
					$ret = "{\"result\":{\"password\":\"" . $pass . "\",\"master\":\"" . $data->{'params'}->{'master'} . "\"}}";
				}
			}
			break;
		case "Password.Add":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("master", "site", "new"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				$result = mysql_query("SELECT `password` FROM `passwords` WHERE `site`='" . mysql_real_escape_string($data->{'params'}->{'site'}) . "';");
				if(mysql_num_rows($result) === 0) {
					mysql_query("INSERT INTO `passwords` (site, password) VALUES ('" . mysql_real_escape_string($data->{'params'}->{'site'}) . "', '" . encrypt($data->{'params'}->{'new'}, $data->{'params'}->{'master'}) . "');");
					$ret = "{\"result\":{\"message\":\"Password inserted successfully.\"}}";
				} else {
					$ret = "{\"error\":{\"code\":-5,\"message\":\"Site not in database.\",\"data\":{}}}";
				}
			}
			break;

		/* XBMC */
		case "XBMC.Pause":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.PlayPause\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Mute":
		case "XBMC.Unmute":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetMute\", \"params\": { \"mute\": " . ($method === "XBMC.Mute" ? "true" : "false") . " }, \"id\": 1}");

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Next":
		case "XBMC.Previous":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.Go" . ($method === "XBMC.Next" ? "Next" : "Previous") . "\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Volume":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(($message = validate_parameters($params, array("volume"))) !== "") {
				$ret = "{\"code\":-4,\"message\":\"Incorrect parameters.\",\"data\":{\"message\":\"" . $message . "\"}}";
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetVolume\", \"params\": { \"volume\": " . $data->{'params'}->{'volume'} . " }, \"id\": 1}");

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Shuffle":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.Shuffle\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.GetPlayer":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$result = xbmc_request("{\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"params\": { }, \"id\": 1}");
				
				$resultJSON = json_decode($result);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":" . json_encode($resultJSON->{'result'}) . "}}";
			}
			break;

		/* Unknown command */
		default:
			$ret = "{\"error\":{\"code\":-1,\"message\":\"Unknown command.\",\"data\":{}}}";
			break;
	}

	echo $ret;

	function validate_parameters($params, $valid) {
		if(count($params) > count(valid)) {
			return "Too many parameters."
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
			$message = "Missing parameters '" . join("', '", array_keys($missing)) . "'";
		}

		if(count($empty) !== 0) {
			if(count($message) !== 0) {
				$message .= ", and parameters '";
			} else {
				$message = "Parameters '";
			}

			$message .= join("', '", array_keys($empty)) . "' cannot be empty";
		}

		if(count($message) !== 0) {
			$message .= ".";
		}

		return $message;
	}

	function xbmc_request($data) {
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
