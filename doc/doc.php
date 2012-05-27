<?php
	$template = file_get_contents('template.md');
	$fp = fopen('README.md', 'w');

	fwrite($fp, $template);

	$json = json_decode(file_get_contents("../server_methods.json"));

	foreach($json as $namespace) {
		fwrite($fp, "### " . $namespace->namespace . "\n\n");

		foreach($namespace->methods as $method) {
			fwrite($fp, "**" . $method->method . "**\n\n");
			fwrite($fp, $method->description . "\n\n");

			if(count($method->parameters) == 0) {
				fwrite($fp, "*Parameters:* `none`\n\n");
			} else {
				fwrite($fp, "*Parameters:*  \n");

				foreach($method->parameters as $parameter) {
					fwrite($fp, "`" . $parameter->name . " (" . $parameter->type . ")`, " . $parameter->description . "  \n");
				}

				fwrite($fp, "\n");
			}

			if(count($method->returns) == 0) {
				fwrite($fp, "*Returns:* `none`\n\n");
			} else {
				fwrite($fp, "*Returns:*  \n");

				foreach($method->returns as $return) {
					fwrite($fp, "`" . $return->name . " (" . $return->type . ")`, " . $return->description . "  \n");
				}

				fwrite($fp, "\n");
			}
		}
	}

	fclose($fp);
?>
