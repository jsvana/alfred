<?php
	$fp = fopen('test.md', 'w');

	$json = json_decode(file_get_contents("server_methods.json"));

	foreach($json as $namespace) {
		fwrite($fp, "### " . $namespace->namespace . "\n\n");

		foreach($namespace->methods as $method) {
			fwrite($fp, "**" . $method->method . "**\n\n");
			fwrite($fp, $method->description . "\n\n");

			if(count($method->parameters) == 0) {
				fwrite($fp, "*Parameters:* `none`\n\n");
			} else {
				fwrite($fp, "*Parameters:*\n");

				foreach($method->parameters as $parameter) {
					fwrite($fp, "`" . $parameter->name . " (" . $parameter->type . ")`, " . $parameter->description . "  \n");
				}
			}
		}
	}

	fwrite($fp, $json[0]->namespace);

	fclose($fp);
?>