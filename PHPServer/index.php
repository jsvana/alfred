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

	$ret = "";

	switch($method) {
		case "App.Login":
			$username = $data->{'params'}->{'username'};
			$password = $data->{'params'}->{'password'};

			if(mysql_num_rows(mysql_query("SELECT `username` FROM `users` WHERE `username`='" . mysql_real_escape_string($username) . "' AND `password`='" . md5($password) . "';")) > 0) {
				$key = md5($username . $password . date());
				$_SESSION['key'] = $key;

				$ret = "{\"result\":{\"key\":\"" . $key . "\"}}";
			} else {
				$ret = "{\"error\":{\"code\":-2,\"message\":\"Incorrect username or password.\",\"data\":{}}}";
			}
			break;
		case "Password.Retrieve":
			if($data->{'key'} !== $_SESSION['key']) {
				$ret = "{\"error\":{\"code\":-3,\"message\":\"Not authenticated.\",\"data\":{}}}";
			} else if(!isset($data->{'params'}->{'master'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'master' not set.\"}}}";
			} else if(!isset($data->{'params'}->{'site'})) {
				$ret = "{\"error\":{\"code\":-4,\"message\":\"Invalid parameters.\",\"data\":{\"message\":\"Parameter 'site' not set.\"}}}";
			} else {
				$result = mysql_query("SELECT `password` FROM `passwords` WHERE `site`='" . mysql_real_escape_string($data->{'params'}->{'site'} . "';"));
				if(mysql_num_rows($result) === 0) {
					$ret = "{\"error\":{\"code\":-5,\"message\":\"Site not in database.\",\"data\":{}}}";
				} else {
					$row = mysql_fetch_assoc($result);
					$pass = decrypt($row['password'], $data->{'params'}->{'master'});
					$ret = "{\"result\":{\"password\":\"" . $pass . "\"}}";
				}
			}
			break;
		default:
			$ret = "{\"error\":{\"code\":-1,\"message\":\"Unknown command.\",\"data\":{}}}";
			break;
	}

	echo $ret;

	function decrypt($crypt, $key) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($crypt), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
?>
