<?php
	$data = json_decode($_POST['json']);
	
	$method = $data->{'method'};

	mysql_connect("localhost", "alfred", "my_cocaine");
	mysql_select_db("alfred");

	$ret = "";

	switch($method) {
		case "App.Login":
			$username = $data->{'params'}->{'username'};
			$password = $data->{'params'}->{'password'};

			if(mysql_num_rows(mysql_query("SELECT `username` FROM `users` WHERE `username`='" . mysql_real_escape_string($username) . "' AND `password`='" . md5($password) . "';")) > 0) {
				$ret = "{\"key\":\"" . md5($username . $password . date()) . "\"}";
			} else {
				$ret = "{\"error\":\"Incorrect username or password.\"}";
			}
			break;
		default:
			$ret = "{\"error\":{\"code\":-1,\"message\":\"Unknown command\",\"data\":{}}}";
			break;
	}

	echo $ret;
?>
