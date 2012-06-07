<?php
include('../config.php');
require 'globals.php';
require 'oauth_helper.php';

// Callback can either be 'oob' or a url
$verifier = '1143420932';
$token = '9FAzmxVSHunsZJCZQu';

// Get the request token using HTTP GET and HMAC-SHA1 signature
$retarr = get_request_token($BITBUCKET_KEY, $BITBUCKET_SECRET_KEY,
                            $verifier, $token, false, true, true);
var_dump($retarr);
    print "https://bitbucket.org/api/1.0/oauth/authenticate/?" . rfc3986_decode($retarr);

exit(0);

/**
 * Get a request token.
 * @param string $consumer_key obtained when you registered your app
 * @param string $consumer_secret obtained when you registered your app
 * @param string $callback callback url can be the string 'oob'
 * @param bool $usePost use HTTP POST instead of GET
 * @param bool $useHmacSha1Sig use HMAC-SHA1 signature
 * @param bool $passOAuthInHeader pass OAuth credentials in HTTP header
 * @return array of response parameters or empty array on error
 */
function get_request_token($consumer_key, $consumer_secret, $verifier, $token, $usePost=false, $useHmacSha1Sig=true, $passOAuthInHeader=false)
{
  $retarr = array();  // return value
  $response = array();

  $url = 'https://bitbucket.org/api/1.0/oauth/access_token/';
  $params['oauth_version'] = '1.0';
  $params['oauth_nonce'] = mt_rand();
  $params['oauth_timestamp'] = time();
  $params['oauth_consumer_key'] = $consumer_key;
  $params['oauth_verifier'] = $verifier;
  $params['oauth_token'] = $token;

  // compute signature and add it to the params list
  if ($useHmacSha1Sig) {
    $params['oauth_signature_method'] = 'HMAC-SHA1';
    $params['oauth_signature'] =
      oauth_compute_hmac_sig($usePost? 'POST' : 'GET', $url, $params,
                             $consumer_secret, null);
  } else {
    $params['oauth_signature_method'] = 'PLAINTEXT';
    $params['oauth_signature'] =
      oauth_compute_plaintext_sig($consumer_secret, null);
  }

  $query_str = "?";

  foreach($params as $key => $value) {
  	$query_str .= $key . "=" . urlencode($value) . "&";
  }

  $query_str = substr($query_str, 0, -1);

  // POST or GET the request
  if ($usePost) {
    $request_url = $url;
    logit("getreqtok:INFO:request_url:$request_url");
    logit("getreqtok:INFO:post_body:$query_str");
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    $response = do_post($request_url, $query_parameter_string, 80, $headers);
  } else {
  	$request_url = $url . $query_str;
	echo $request_url;
	$ch = curl_init($request_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, 'Accepts: text/json');
	$response = curl_exec($ch);
	curl_close($ch);
  }

  return $response;
}
?>
