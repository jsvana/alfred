<?php
	session_start();

	$data = json_decode($_POST['json']);

	if(!isset($data) || !isset($data->{'method'})) {
		echo "{\"error\":{\"code\":-1,\"message\":\"Malformed command.\",\"data\":{}}}";
		return;
	}

	$method = $data->{'method'};

	mysql_connect("localhost", "alfred", "my_cocaine");
	mysql_select_db("alfred");

	$XBMC_USERNAME = "xbmc";
	$XBMC_PASSWORD = "1123581321!";
	$XBMC_HOST = "67.149.240.147";
	$XBMC_PORT = "8080";

	$ret = "";

	switch($method) {
		case "App.Login":
			$username = $data->{'params'}->{'username'};
			$password = $data->{'params'}->{'password'};

			if(mysql_num_rows(mysql_query("SELECT `username` FROM `users` WHERE `username`='" . mysql_real_escape_string($username) . "' AND `password`='" . md5($password) . "';")) > 0) {
				$key = md5($username . $password . time());
				mysql_query("INSERT INTO `sessions` (api_key, expiration) VALUES ('" . $key . "', DATE_ADD(NOW(), INTERVAL 1 HOUR));");

				$ret = "{\"result\":{\"key\":\"" . $key . "\"}}";
			} else {
				$ret = "{\"error\":{\"code\":-2,\"message\":\"Incorrect username or password.\",\"data\":{}}}";
			}
			break;
		case "Password.Retrieve":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(!isset($data->{'params'}->{'master'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'master' not set.\"}}}";
			} else if(!isset($data->{'params'}->{'site'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'site' not set.\"}}}";
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
			} else if(!isset($data->{'params'}->{'master'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'master' not set.\"}}}";
			} else if(!isset($data->{'params'}->{'site'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'site' not set.\"}}}";
			} else if(!isset($data->{'params'}->{'new'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'new' not set.\"}}}";
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
		case "XBMC.Pause":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Player.PlayPause\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$result = curl_exec($ch);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Mute":
		case "XBMC.Unmute":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetMute\", \"params\": { \"mute\": " . ($method === "XBMC.Mute" ? "true" : "false") . " }, \"id\": 1}");

				$result = curl_exec($ch);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Next":
		case "XBMC.Previous":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Player.Go" . ($method === "XBMC.Next" ? "Next" : "Previous") . "\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$result = curl_exec($ch);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Volume":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(!isset($data->{'params'}->{'volume'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'volume' not set.\"}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Application.SetVolume\", \"params\": { \"volume\": " . $data->{'params'}->{'volume'} . " }, \"id\": 1}");

				$result = curl_exec($ch);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.Shuffle":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Player.Shuffle\", \"params\": { \"playerid\": 0 }, \"id\": 1}");

				$result = curl_exec($ch);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\"}}";
			}
			break;
		case "XBMC.GetPlayer":
			if(!isset($data->{'key'}) || $data->{'key'} === "" || !session_authenticated($data->{'key'})) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else {
				$ch = curl_init();

				curl_setopt($ch, CURLOPT_URL, "http://" . $XBMC_USERNAME . ":" . $XBMC_PASSWORD . "@" . $XBMC_HOST . ":" . $XBMC_PORT . "/jsonrpc");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, "{\"jsonrpc\": \"2.0\", \"method\": \"Player.GetActivePlayers\", \"params\": { }, \"id\": 1}");

				$result = curl_exec($ch);
				
				$resultJSON = json_decode($result);

				curl_close($ch);

				$ret = "{\"result\":{\"message\":\"Command sent.\", \"data\":" . json_encode($resultJSON->{'result'}) . "}}";
			}
			break;
		default:
			$ret = "{\"error\":{\"code\":-1,\"message\":\"Unknown command.\",\"data\":{}}}";
			break;
	}

	echo $ret;

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
