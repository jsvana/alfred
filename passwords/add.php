<?php
	mysql_connect("localhost", "alfred", "my_cocaine");
	mysql_select_db("alfred");

	$pass = $_POST['master'];
	$clear = $_POST['new'];
	$site =  $_POST['site'];
	echo $pass . ", " . $clear . ", " . $site;

	$row = mysql_fetch_assoc(mysql_query("SELECT `master_password` FROM config;"));

	if($row['master_password'] !== md5($pass)) {
		echo "{\"error\":\"Not authenticated.\"";
		return;
	}

	if(mysql_num_rows(mysql_query("SELECT `password` FROM `passwords` WHERE site='" . mysql_real_escape_string($site) . "';")) > 0) {
		echo "{\"error\":\"Site already exists in database.\"}";
	} else {
		$crypted = encrypt($clear, $pass);
		mysql_query("INSERT INTO `passwords` (site, password) VALUES ('" . mysql_real_escape_string($site) . "', '" . $crypted . "');");
		echo "{\"error\":\"\"}";
	}

	function encrypt($sValue, $sSecretKey) {
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, $sValue, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

	function decrypt($sValue, $sSecretKey) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, base64_decode($sValue), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
?>
