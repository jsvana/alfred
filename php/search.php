<?php
	require_once('alfred.php');
	require_once('workflows.php');

	$apiKey = alfred_login('guest', 'hunter2');

	if ($apiKey === null) {
		exit(1);
	}

	$w = new Workflows();

	switch ($argv[1]) {

		default:
			$w->result('1', 'none', 'Unknown command: ' . $argv[1], 'Try another!', '');
			break;
	}

	echo $w->toxml();
?>
