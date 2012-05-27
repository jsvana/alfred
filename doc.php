<?php
	$fp = fopen('test.md', 'w');

	$json = file_get_contents("server_methods.json");

	fwrite($fp, $json);

	fclose($fp);
?>