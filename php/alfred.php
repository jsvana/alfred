<?php
	function alfred_request($method, $params, $apiKey = '') {
		$url = 'http://alf.re/d/';
		$data = '{"params":{';

		foreach ($params as $key => $value) {
			$data .= '"' . $key . '":"' . $value . '",';
		}

		$data = substr($data, 0, -1);

		$data .= '},"key":"' . $apiKey . '","alfred":"0.1","method":"' . $method . '"}';

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'text/plain'));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$data = curl_exec($ch);
		return $data;
	}

	function alfred_login($username, $password) {
		$params = array('username' => $username, 'password' => $password);

		$data = alfred_request('Alfred.Login', $params);

		$json = json_decode($data);

		if ($json->code < 0) {
			return null;
		}

		return $json->data->key;
	}
?>
