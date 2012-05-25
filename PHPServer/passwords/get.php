<?php
	mysql_connect("localhost", "alfred", "my_cocaine");
	mysql_select_db("alfred");

	$pass = $_POST['master'];
	$site =  $_POST['site'];

	$row = mysql_fetch_assoc(mysql_query("SELECT `master_password` FROM config;"));

	if($row['master_password'] !== md5($pass)) {
		echo "{\"password\":\"\",\"error\":\"Not authenticated.\"}";
		return;
	}
		
	$result = mysql_query("SELECT `password` FROM `passwords` WHERE  site='" . mysql_real_escape_string($site) . "';");
	if(mysql_num_rows($result) === 0) {
		echo "{\"password\":\"\",\"error\":\"Site does not exist in database.\"}";
	} else {
		$row = mysql_fetch_assoc($result);
		$clearText = decrypt($row['password'], $pass);
		if(isset($_POST['json'])) {
			echo "{\"password\":\"" . $clearText . "\",\"error\":\"\"}";
		} else {
			echo "Password: " . $clearText . "<br>";
		}
	}

	function encrypt($sValue, $sSecretKey) {
		return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, $sValue, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
	}

	function decrypt($sValue, $sSecretKey) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, base64_decode($sValue), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
	}
?>
