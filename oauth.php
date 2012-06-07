<?php
	require_once ("OAuth.php");
	require_once ("PHPServer/config.php");

	$key = $BITBUCKET_KEY;//'<your app's API key>';
	$secret = $BITBUCKET_SECRET_KEY;//'<your app's secret>';

	$base_url = "http://psg.mtu.edu:21516/alfred/PHPServer/";
	$request_token_endpoint = 'https://bitbucket.org/api/1.0/oauth/request_token/';
	$authorize_endpoint = 'https://bitbucket.org/api/1.0/oauth/authenticate/';

	$test_consumer = new OAuthConsumer($key, $secret, NULL);

	//prepare to get request token

	$sig_method = new OAuthSignatureMethod_RSA_SHA1();
	$parsed = parse_url($request_token_endpoint);
	$params = array(callback => $base_url);
	parse_str($parsed['query'], $params);

	$req_req = OAuthRequest::from_consumer_and_token($test_consumer, NULL, "GET", $request_token_endpoint, $params);
	$req_req->sign_request($sig_method, $test_consumer, NULL);
	echo $req_req->to_url() . "&oauth_callback=" . urlencode($base_url) . "<br>";

	$req_token = doHttpRequest ($req_req->to_url());
	echo $req_token . "<br>";

	//assuming the req token fetch was a success, we should have
	//oauth_token and oauth_token_secret

	parse_str ($req_token,$tokens);

	$oauth_token = $tokens['oauth_token'];
	$oauth_token_secret = $tokens['oauth_token_secret'];

	//echo $oauth_token . ", " . $oauth_token_secret;
	
	$callback_url = "$base_url/callback.php?key=$key&token=$oauth_token&token_secret=$oauth_token_secret&endpoint=" . urlencode($authorize_endpoint);

	$auth_url = $authorize_endpoint . "?oauth_token=$oauth_token&oauth_callback=".urlencode($callback_url);

	echo $auth_url;

	//echo file_get_contents($auth_url);

	//Forward us to justin.tv for auth
	//Header("Location: $auth_url");
	
	function doHttpRequest($urlreq) {
		$ch = curl_init();

		// set URL and other appropriate options
		curl_setopt($ch, CURLOPT_URL, "$urlreq");
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		// grab URL and pass it to the browser
		$request_result = curl_exec($ch);

		// close cURL resource, and free up system resources
		curl_close($ch);

		return $request_result;
	}
?>
