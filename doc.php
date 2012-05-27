<?php
	$fp = fopen('test.md', 'w');

	$json = json_decode(file_get_contents("server_methods.json"));

	foreach($json as $namespace) {
		fwrite($fp, "### " . $namespace->namespace . "\n\n");

		foreach($namespace->methods as $method) {
			fwrite($fp, "**" . $method->method . "**\n\n");
			fwrite($fp, $method->description . "\n\n");
		}
	}

	fwrite($fp, $json[0]->namespace);

	fclose($fp);
?>