<?php

$url = $_SERVER['REQUEST_URI'];

$ua = $_SERVER["HTTP_USER_AGENT"];
$os = "n/a";
if (strpos($ua, 'Android')) {
	$os = "android";
} else if (strpos($ua, 'BlackBerry')) {
	$os = "blackberry";
} else if (strpos($ua, 'iPhone')) {
	$os = "iphone";
} else if (strpos($ua, 'Palm')) {
	$os = "palm";
} else if (strpos($ua, 'Linux')) {
	$os = "linux";
} else if (strpos($ua, 'Macintosh')) {
	$os = "mac";
} else if (strpos($ua, 'Windows')) {
	$os = "windows";
}

error_reporting(E_ALL ^ E_NOTICE);

function getMIMEType($extension) {
	switch($extension) {
		case "jar":
			return "application/java-archive";
			break;
		case "exe":
			return "application/octet-stream";
			break;
		case "zip":
			return "application/zip";
			break;
		case "mp3":
			return "audio/mpeg";
			break;
		case "mid":
			return "audio/midi";
			break;
		case "ogg":
			return "application/ogg";
			break;
		case "apk":
			return "application/vnd.android.package-archive";
			//return "application/zip";
			break;
		case "swf":
			return " application/x-shockwave-flash";
			break;
		default:
			return "application/text";
			break;
	}
}

$platformsForKeys = [
	"steam" => "Steam",
	"switch" => "Nintendo Switch"
];
function util_getValidPlatformsForProjectKeys() {
	global $platformsForKeys;
	return $platformsForKeys;
}
function util_isValidPlatformForProjectKeys($platform) {
	global $platformsForKeys;
	if (in_array($platform, array_keys($platformsForKeys))) {
		return true;
	}
	return false;
}
function util_isValidSubplatform($platform, $subplatform) {
	if ($platform == "switch") {
		if (in_array($subplatform, array_keys(util_listNintendoRegions()))) {
			return true;
		}
		return false;
	}
	return true;
}
function util_isValidKeyFormat($platform, $key, &$result) {
	if ($platform == "steam") {
		if (strlen($key) != 17) {
			$result = api_error("Steam keys must be in format XXXXX-XXXXX-XXXXX, and one per each line.");
			return false;
		}
		return true;
	}
	else if ($platform == "switch") {
		if (strlen($key) != 16) {
			$result = api_error("Switch keys must be 16 characters long and one per each line.");
			return false;
		}
		return true;
	}
	$result = api_error("Key format validation not implemented for platform: " . $platform);
	return false;
}
function util_containsKeywords($haystack, $keywordsRaw) {
	$haystack = strtolower($haystack);
	$keywordsRaw = strtolower($keywordsRaw);

	if (strlen($keywordsRaw) == 0) { return false; }
	if (strpos($keywordsRaw, ",") == FALSE) {
		echo $haystack . " - " . $keywordsRaw . "<br/>";
		return (strpos($haystack, $keywordsRaw) !== FALSE);
	}
	$bits = explode(",", $keywordsRaw);
	print_r($bits);
	for ($i = 0; $i < count($bits); $i++) {
		$bit = trim($bits[$i]);
		echo $bit . "<br/>";
		if (strpos($haystack, $bit) !== FALSE) {
			return true;
		}
	}
	return false;
}
function util_getFirstNameForObject($typeObj) {
	if ($typeObj['firstname'] && strlen($typeObj['firstname']) > 0) return $typeObj['firstname'];
	if ($typeObj['name_override'] && strlen($typeObj['name_override']) > 0) return $typeObj['name_override'];
	if ($typeObj['name'] && strlen($typeObj['name']) > 0) return $typeObj['name'];
	if ($typeObj['email']  && strlen($typeObj['email']) > 0) return $typeObj['email'];
	return "Unknown";
}
function util_getFullNameForObject($typeObj) {
	if ($typeObj['firstname'] && strlen($typeObj['firstname']) > 0) return $typeObj['firstname'] . " " . $typeObj['surnames'];;
	if ($typeObj['name_override'] && strlen($typeObj['name_override']) > 0) return $typeObj['name_override'];
	if ($typeObj['twitchUsername'] && strlen($typeObj['twitchUsername']) > 0) return $typeObj['twitchUsername'];
	if ($typeObj['name'] && strlen($typeObj['name']) > 0) return $typeObj['name'];
	if ($typeObj['email']  && strlen($typeObj['email']) > 0) return $typeObj['email'];
	return "Unknown";
}

/** Takes into account decimal points and negative values... **/
function isNumeric($number, $name = "Field", $length = -1)
{
	if (trim($number) == "")
	{
		return false;
	}
	else if (!is_numeric($number))
	{
		return false;
	}
	else if (strpos($number, ".") !== FALSE)
	{
		return false;
	}
	else if (strpos($number, "-") !== FALSE)
	{
		return false;
	}
	if ($length != -1)
	{
		if (strlen($number) != $length)
		{
			return false;
		}
	}
	return true;
}


function util_isImpressPersonType($type) {
	return ($type == 'person' || $type == 'publication' || $type == 'personPublication' || $type == 'youtuber' || $type == 'twitchchannel');
}

function util_isEmail($emailAddress) {
	//if (!preg_match("/([a-z0-9])([-a-z0-9._])+([a-z0-9])\@([a-z0-9])([-a-z0-9_])+([a-z0-9])(\.([a-z0-9])([-a-z0-9_-])([a-z0-9])+)*/i", $emailAddress)) {
	//	return false;
	//}
	//return true;
	return (filter_var($emailAddress, FILTER_VALIDATE_EMAIL));
}

function util_isInteger($integer) {
	if (is_numeric($integer)) {
		if (strpos($integer, '.') === FALSE) {
			return true;
		}
	}
	return false;
}

function util_isAlphaNumeric($string)
{
   //return (bool) preg_match('/^[a-zA-Z0-9]$/', $input);
	$string = strtolower($string);
	// cannot be left empty either.
	if ($string == "") {
		return false;
	}
	$chararray = array(	"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m",
						"n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
						"1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
  	for ($i = 0; $i < strlen($string); $i++) {
		for ($j = 0; $j < count($chararray); $j++) {
			if (in_array($string[$i], $chararray)) {
				//echo $string[$i] . " -- " . $chararray[$j] . "<br>";
				// Move to the next letter (Reset the top level loop)
				continue 2;
			} else {
				// If we get here, The letter isnt valid. Set error and break both loops.
				return false;
				break 2;
			}
		}
	}
	return true;
}

function util_isAlphaNumericWithSpaces($field, $string, $maxlength, $minlength)
{
	return util_isAlphaNumericWithExtras($string, array(" "), $maxlength, $minlength);
}
function util_isAlphaNumericWithExtras($string, $extras, $maxlength, $minlength)
{
	$string = strtolower($string);
	// cannot be left empty either.
	if ($string == "") {
		return false;
	}
	$chararray = array(	"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m",
						"n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z",
						"1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
	$chararray = array_merge($chararray, $extras);
  	for ($i = 0; $i < strlen($string); $i++) {
		for ($j = 0; $j < count($chararray); $j++) {
			if (in_array($string[$i], $chararray)) {
				//echo $string[$i] . " -- " . $chararray[$j] . "<br>";
				// Move to the next letter (Reset the top level loop)
				continue 2;
			} else {
				// If we get here, The letter isnt valid. Set error and break both loops.
				return false;
				break 2;
			}
		}
	}
	if (strlen($string) > $maxlength) {
		return false;
	} else if (strlen($string) < $minlength) {
		return false;
	}
	return true;
}

function serve_file($filename, $contents, $format) {
	header("HTTP/1.1 200 OK\r\n");
	header("Cache-Control: no-cache, must-revalidate\r\n");
	header("Content-Type: " . getMIMEType($format) . "\r\n");
	header("Content-Length: " . strlen($contents) . "\r\n");

	//if ($format != "apk") {
		header("Content-Disposition: attachment; filename=" . str_replace(" ", "_", $filename) . "\r\n");
	//}

	header("Connection: close\r\n\r\n" );

	echo $contents;
}

function is_ssl() {
    if ( isset($_SERVER['HTTPS']) ) {
        if ( 'on' == strtolower($_SERVER['HTTPS']) )
            return true;
        if ( '1' == $_SERVER['HTTPS'] )
            return true;
    } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
        return true;
    }
    return false;
}

srand((double) microtime() * 1000000);
$impresslist_encryption_iv = util_getIV(true);
function util_getIV($new) {
	global $impresslist_encryption_iv;
	if ($new) {
		$impresslist_encryption_iv = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM); // mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC)
		if (strlen($impresslist_encryption_iv) < 32) {
			$impresslist_encryption_iv = str_pad($impresslist_encryption_iv, 32, ".", STR_PAD_RIGHT);
		} else if (strlen($impresslist_encryption_iv) > 32) {
			$impresslist_encryption_iv = substr($impresslist_encryption_iv, 0, 32);
		}


		// ... FFFFFFFFF it keeps generating strings less than 32 chars long.
		$impresslist_encryption_iv = md5($impresslist_encryption_iv);

	}
	return $impresslist_encryption_iv;
}
function util_setIV($iv) {
	global $impresslist_encryption_iv;
	$impresslist_encryption_iv = $iv;
}
function util_getSalt() {
	return strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
}

function util_encrypt($sValue, $sSecretKey) {
    global $impresslist_encryption_iv;
    return rtrim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, $sValue, MCRYPT_MODE_CBC, $impresslist_encryption_iv)), "\0\3");
}
function util_decrypt($sValue, $sSecretKey) {
    global $impresslist_encryption_iv;
    return rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $sSecretKey, base64_decode($sValue), MCRYPT_MODE_CBC, $impresslist_encryption_iv), "\0\3");
}

// ----------------------------------------------------------------------------
// Twitter
// ----------------------------------------------------------------------------
function twitterDate($d) {
	$bits = explode(" ", $d);
	return $bits[2] . " " . $bits[1];
}
// convert @mentions, #hashtags, and URLs (w/ or w/o protocol) into links
function twitterLinks($text)
{
	// convert URLs into links
	$text = preg_replace("#(https?://([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+]*)?)#i", "<a href='$1' target='_blank'>$1</a>", $text);
	// convert protocol-less URLs into links
	$text = preg_replace("#(?!https?://|<a[^>]+>)(^|\s)(([-a-z0-9]+\.)+[a-z]{2,5}([/?][-a-z0-9!\#()/?&+.]*)?)\b#i", "$1<a href='http://$2'>$2</a>", $text);
	// convert @mentions into follow links
	$text = preg_replace("#(?!https?://|<a[^>]+>)(^|\s)(@([_a-z0-9\-]+))#i", "$1<a href=\"http://twitter.com/$3\" title=\"Follow $3\" target=\"_blank\">@$3</a>", $text);
	// convert #hashtags into tag search links
	$text = preg_replace("#(?!https?://|<a[^>]+>)(^|\s)(\#([_a-z0-9\-]+))#i", "$1<a href='http://twitter.com/search?q=%23$3' title='Search tag: $3' target='_blank'>#$3</a>", $text);
	return $text;
}

function twitter_getConnectionWithAccessToken($oauth_token, $oauth_token_secret) {
  	global $twitter_consumerKey;
  	global $twitter_consumerSecret;
  	$connection = new Abraham\TwitterOAuth\TwitterOAuth($twitter_consumerKey, $twitter_consumerSecret, $oauth_token, $oauth_token_secret);
  	return $connection;
}

function twitter_countFollowers($username)
{
	global $twitter_oauthToken;
	global $twitter_oauthSecret;
	if (strlen($username) == 0) { return 0; }

	$twitter_connection = twitter_getConnectionWithAccessToken($twitter_oauthToken, $twitter_oauthSecret);
	$twitter_content = $twitter_connection->get("users/show", ['screen_name' => $username ]);
	if (isset($twitter_content->errors)) {
		//print_r($twitter_content);
		return 0;
	}
	//echo json_encode($twitter_content);
	return $twitter_content->followers_count;
}
function twitter_getUserId($username)
{
	global $twitter_oauthToken;
	global $twitter_oauthSecret;
	if (strlen($username) == 0) { return 0; }

	$twitter_connection = twitter_getConnectionWithAccessToken($twitter_oauthToken, $twitter_oauthSecret);
	$twitter_content = $twitter_connection->get("users/show", ['screen_name' => $username ]);
	if (isset($twitter_content->errors)) {
		//print_r($twitter_content);
		return 0;
	}
	//echo json_encode($twitter_content);
	return $twitter_content->id;
}

function twitter_getUserInfoById($oauthtoken, $oauthsecret, $id) {
	$url = "users/show";
	$twitter_connection = twitter_getConnectionWithAccessToken($oauthtoken, $oauthsecret);
	return $twitter_connection->get($url, array("user_id" => $id));
}
function twitter_sendDirectMessage($oauthtoken, $oauthsecret, $recipientUserId, $message) {
	$url = "direct_messages/events/new";
	$data = array(
		"event" => array(
			"type" => "message_create",
			"message_create" => array(
				"target" => array(
					"recipient_id" => "" . $recipientUserId
				),
				"message_data" => array(
					"text" => $message
				)
			)
		)
	);
	//print_r($data);
	$twitter_connection = twitter_getConnectionWithAccessToken($oauthtoken, $oauthsecret);
	return $twitter_connection->post($url, $data, true);
}

function twitter_postStatus($oauthtoken, $oauthsecret, $status) {
	$url = "statuses/update";
	$twitter_connection = twitter_getConnectionWithAccessToken($oauthtoken, $oauthsecret);
	return $twitter_connection->post($url, array("status" => $status));
}

function twitter_retweetStatus($oauthtoken, $oauthsecret, $status_id) {
	$url = "statuses/retweet/{$status_id}";
	$twitter_connection = twitter_getConnectionWithAccessToken($oauthtoken, $oauthsecret);
	return $twitter_connection->post($url, array());
}
function twitter_postStatusWithImage($oauthtoken, $oauthsecret, $status, $imagefiles) {

	if (!is_array($imagefiles)) {
		$imagefiles = array($imagefiles);
	}
	$twitter_connection = twitter_getConnectionWithAccessToken($oauthtoken, $oauthsecret);

	$media_ids = array();
	for($i = 0; $i < count($imagefiles); $i++)
	{
		$file = $_SERVER['DOCUMENT_ROOT'] . "/" . $imagefiles[$i];
		$image = $twitter_connection->upload('media/upload', array('media' => $file ) );
		$media_ids[] = $image->media_id_string;
	}

	$url = "statuses/update";
	return $twitter_connection->post($url, array(
		"status" => $status,
		"media_ids" => implode(",", $media_ids)
	));

}
function twitter_helpConfiguration() {
	global $twitter_oauthToken;
	global $twitter_oauthSecret;

	$url = "help/configuration";
	$twitter_connection = twitter_getConnectionWithAccessToken($twitter_oauthToken, $twitter_oauthSecret);
	return $twitter_connection->get($url);
}
function twitter_helpConfigurationSave($config = false) {
	global $db;

	if ($config == false) {
		$config = twitter_helpConfiguration();
	}
	if (!$config || ( $config && isset($config->errors))) {
		return false;
	}
	$rjson = json_encode($config);

	$stmt = $db->prepare(" UPDATE settings SET `value` = :value WHERE `key` = :key; ");
	$stmt->bindValue(":value", 	$rjson, 					Database::VARTYPE_STRING);
	$stmt->bindValue(":key",  	"twitter_configuration", 	Database::VARTYPE_STRING);
	return $stmt->execute();
}

function util_get_github_releases() {

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, "https://api.github.com/repos/ashleygwinnell/impresslist/releases");
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$contents = curl_exec($ch);
	if (curl_errno($ch)) {
		return curl_error($ch);
	} else {
		curl_close($ch);
	}
	return $contents;
}

function url_get_contents($url) {

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 5000);
	curl_setopt ($ch, CURLOPT_FAILONERROR, true);
	curl_setopt ($ch, CURLOPT_VERBOSE, true);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt ($ch, CURLOPT_MAXREDIRS, 100);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	$contents = curl_exec($ch);
	if (curl_errno($ch)) {
		echo curl_error($ch);
		echo "\n<br />";
		$contents = '';
	} else {
		curl_close($ch);
	}

	if (!is_string($contents) || !strlen($contents)) {
		//echo "Failed to get contents.";
		$contents = '';
	}

	return $contents;
}

// ----------------------------------------------------------------------------
// Twitch
// ----------------------------------------------------------------------------
function twitch_apiCall($url) {
	global $twitch_apiKey;
	global $twitch_apiSecret;
	//echo $url;

	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt ($ch, CURLOPT_TIMEOUT_MS, 5000);
	curl_setopt ($ch, CURLOPT_FAILONERROR, true);
	curl_setopt ($ch, CURLOPT_VERBOSE, true);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt ($ch, CURLOPT_MAXREDIRS, 100);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    'Client-ID: ' . $twitch_apiKey
	));
	$contents = curl_exec($ch);
	if (curl_errno($ch)) {
		echo curl_error($ch);
		echo "\n<br />";
		$contents = null;
	} else {
		curl_close($ch);
	}

	if (!is_string($contents) || !strlen($contents)) {
		$contents = null;
		return $contents;
	}
	return json_decode($contents, JSON_ASSOC);
}

function twitch_getUsers($userIds, $requestEmails=true) {
	if (!is_array($userIds)){
		return twitch_apiCall("https://api.twitch.tv/helix/users?id=" . urlencode($userIds));
	}
	$url = "https://api.twitch.tv/helix/users?";
	for($i = 0; $i < count($userIds); $i++) {
		$url .= "id=" . urlencode($userIds[$i]);
		if ($i < count($userIds) - 1) {
			$url .= "&";
		}
	}
	// if ($requestEmails) {
	// 	$url .= "&scope=user:read:email";
	// }
	return twitch_apiCall($url);
}
function twitch_getUsersFromLogin($userLogins) {
	if (!is_array($userLogins)){
		return twitch_apiCall("https://api.twitch.tv/helix/users?login=" . urlencode($userLogins));
	}
	$url = "https://api.twitch.tv/helix/users?";
	for($i = 0; $i < count($userLogins); $i++) {
		$url .= "login=" . urlencode($userLogins[$i]);
		if ($i < count($userLogins) - 1) {
			$url .= "&";
		}
	}
	return twitch_apiCall($url);
}
function twitch_countSubscribers($userId) {
	$data = twitch_apiCall("https://api.twitch.tv/helix/users/follows?to_id=" . $userId);
	if ($data) {
		return $data['total'];
	}
	return 0;
}
function twitch_getStreamsForGame($gameId) {
	return twitch_apiCall("https://api.twitch.tv/helix/streams?game_id=" . $gameId);
}
function twitch_getStreamsForUsers($userIds) {
	if (!is_array($userIds)){
		return twitch_apiCall("https://api.twitch.tv/helix/streams?user_id=" . urlencode($userIds));
	}
	$url = "https://api.twitch.tv/helix/streams?";
	for($i = 0; $i < count($userIds); $i++) {
		$url .= "user_id=" . urlencode($userIds[$i]);
		if ($i < count($userIds) - 1) {
			$url .= "&";
		}
	}
	return twitch_apiCall($url);
}
function twitch_getStreamsMetadataForUsers($userIds) {
	if (!is_array($userIds)){
		return twitch_apiCall("https://api.twitch.tv/helix/streams/metadata?user_id=" . urlencode($userIds));
	}
	$url = "https://api.twitch.tv/helix/streams/metadata?";
	for($i = 0; $i < count($userIds); $i++) {
		$url .= "user_id=" . urlencode($userIds[$i]);
		if ($i < count($userIds) - 1) {
			$url .= "&";
		}
	}
	return twitch_apiCall($url);
}
function twitch_findGamesByNames($names) {
	if (!is_array($names)){
		return twitch_apiCall("https://api.twitch.tv/helix/games?name=" . urlencode($names));
	}

	$url = "https://api.twitch.tv/helix/games?";
	for($i = 0; $i < count($names); $i++) {
		$url .= "name=" . urlencode($names[$i]);
		if ($i < count($names) - 1) {
			$url .= "&";
		}
	}
	return twitch_apiCall($url);
}
function twitch_getVideosOfGame($gameId) {
	return twitch_apiCall("https://api.twitch.tv/helix/videos?game_id=" . $gameId);
}
function twitch_getVideosForUser($userIds) {
	//if (!is_array($userIds)){
		return twitch_apiCall("https://api.twitch.tv/helix/videos?user_id=" . urlencode($userIds));
	// }
	// $url = "https://api.twitch.tv/helix/videos?";
	// for($i = 0; $i < count($userIds); $i++) {
	// 	$url .= "user_id=" . urlencode($userIds[$i]);
	// 	if ($i < count($userIds) - 1) {
	// 		$url .= "&";
	// 	}
	// }
	// return twitch_apiCall($url);
}
function twitch_getClipsOfGame($gameId) {
	return twitch_apiCall("https://api.twitch.tv/helix/clips?game_id=" . $gameId);
}
function twitch_getClipsForUser($userId) {
	return twitch_apiCall("https://api.twitch.tv/helix/clips?broadcaster_id=" . $userId);
}

function db_try_add_twitch_channel($twitchId, $twitchDescription, $twitchBroadcasterType, $twitchProfileImage, $twitchOfflineImage, $twitchUsername, $displayname, $email, $viewCount, $subCount) {
	global $db;

	$stmt = $db->prepare("INSERT INTO twitchchannel (`id`, `twitchId`, `twitchDescription`, `twitchBroadcasterType`, `twitchProfileImageUrl`, `twitchOfflineImageUrl`, `twitchUsername`,  `name`, `email`, priorities, subscribers, `views`, twitter, twitter_followers, twitter_updatedon, notes, lang, lastpostedon, lastpostedon_updatedon, removed, lastscrapedon)
											VALUES ( NULL, :twitchId, :twitchDescription, :twitchBroadcasterType, :twitchProfileImage, :twitchOfflineImage, :twitchUsername, :name,  :email, :priorities, :subscribers, 			 :views,  '', 		0, 					0,      		'',  	'', 	0,  		0, 						0, 			0
						); ");
	$stmt->bindValue(":twitchId", $twitchId, Database::VARTYPE_INTEGER);
	$stmt->bindValue(":twitchDescription", $twitchDescription, Database::VARTYPE_STRING);
	$stmt->bindValue(":twitchBroadcasterType", $twitchBroadcasterType, Database::VARTYPE_STRING);
	$stmt->bindValue(":twitchProfileImage", $twitchProfileImage, Database::VARTYPE_STRING);
	$stmt->bindValue(":twitchOfflineImage", $twitchOfflineImage, Database::VARTYPE_STRING);
	$stmt->bindValue(":twitchUsername", $twitchUsername, Database::VARTYPE_STRING);
	$stmt->bindValue(":name", $displayname, Database::VARTYPE_STRING);
	$stmt->bindValue(":email", $email, Database::VARTYPE_STRING);
	$stmt->bindValue(":priorities", db_defaultPrioritiesString($db), Database::VARTYPE_STRING);
	$stmt->bindValue(":views", $viewCount, Database::VARTYPE_INTEGER);
	$stmt->bindValue(":subscribers", $subCount, Database::VARTYPE_INTEGER);
	$r = $stmt->execute();
	if (!$r) {
		die();
	}
	return $r;
}

function db_try_add_twitch_channel_from_user_result($user) {
	global $db;

	$results = $db->query("SELECT * FROM twitchchannel WHERE twitchId = '" . $user['id'] . "';");
	if (count($results) > 0) {
		return false;
	}

	$subs = twitch_countSubscribers($user['id']);
	db_try_add_twitch_channel(
		$user['id'],
		$user['description'],
		$user['broadcaster_type'],
		$user['profile_image_url'],
		$user['offline_image_url'],
		$user['login'],
		$user['display_name'],
		"",
		$user['view_count'],
		$subs
	);
	return true;

}


// ----------------------------------------------------------------------------
// Youtube
// ----------------------------------------------------------------------------
function youtube_getInformation($channel) {
	if (strlen($channel) == 0) { return 0; }

	$url = "http://gdata.youtube.com/feeds/api/users/" . $channel . "?alt=json";
	$text = file_get_contents($url);
	if (substr($text, 0, 1) != "{") {
		return 0;
	}
	$content = json_decode($text, JSON_ASSOC);
	$result = array(
		"name" => $content['entry']['title']['$t'],
		"description" => strip_tags($content['entry']['content']['$t']),
		"lastpostedon" => strtotime($content['entry']['updated']['$t']),
		"iconurl" => $content['entry']['media$thumbnail']['url'],
		"subscribers" => $content['entry']['yt$statistics']['subscriberCount'],
		"views" => $content['entry']['yt$statistics']['totalUploadViews']
	);

	return $result;
}
function youtube_getUploads($channel) {
	if (strlen($channel) == 0) { return 0; }

	$url = "http://gdata.youtube.com/feeds/api/users/" . $channel . "/uploads?alt=json";
	$text = file_get_contents($url);
	if (substr($text, 0, 1) != "{") {
		return 0;
	}
	$content = json_decode($text, JSON_ASSOC);
	return $content;
}

function util_isLocalhost() {
	return $_SERVER['HTTP_HOST'] == "localhost";
}

function youtube_v3_search($terms, $order = "date", $sinceTimestamp = 0) {
	global $youtube_apiKey;
	if (strlen($terms) == 0) { return 0; }

	$url = "https://www.googleapis.com/youtube/v3/search?key=" . $youtube_apiKey . "&part=snippet&q=" . urlencode($terms) . "&maxResults=50&order=" . $order. "&type=video";
	if ($sinceTimestamp > 0) {
		$url .= "publishedAfter=";
	}

	$text = url_get_contents($url);
	//echo $text;
	if (substr($text, 0, 1) != "{") {
		return 0;
	}
	$content = json_decode($text, JSON_ASSOC);
	//print_r($content);
	return $content;

}
function youtube_v3_getInformation($channel) {
	global $youtube_apiKey;
	if (strlen($channel) == 0) { return 0; }

	$url = "https://www.googleapis.com/youtube/v3/channels?forUsername=" .$channel . "&key=" . $youtube_apiKey . "&part=contentDetails,snippet,statistics&maxResults=50";
	$text = url_get_contents($url);
	if (substr($text, 0, 1) != "{") {
		return 0;
	}
	$content = json_decode($text, JSON_ASSOC);

	if (count($content['items']) == 0) {
		$url = "https://www.googleapis.com/youtube/v3/channels?id=" .$channel . "&key=" . $youtube_apiKey . "&part=contentDetails,snippet,statistics&maxResults=50";
		$text = url_get_contents($url);
		if (substr($text, 0, 1) != "{") {
			return 0;
		}
		$content = json_decode($text, JSON_ASSOC);
	}

	$result = array(
		"id" => $content['items'][0]['id'],
		"name" => $content['items'][0]['snippet']['localized']['title'],
		"description" => strip_tags($content['items'][0]['snippet']['localized']['description']),
		"lastpostedon" => 0, // this has to be got from _v3_getUploads()
		"thumbnail" => $content['items'][0]['snippet']['thumbnails']['default']['url'],
		"iconurl" => $content['items'][0]['snippet']['thumbnails']['default']['url'],
		"subscribers" => $content['items'][0]['statistics']['subscriberCount'],
		"views" => $content['items'][0]['statistics']['viewCount'],
		"videos" => $content['items'][0]['statistics']['videoCount'],
		"playlists" => array(
			"uploads" => $content['items'][0]['contentDetails']['relatedPlaylists']['uploads'],
		)
	);
	return $result;
}

function youtube_v3_getUploads($playlist) {
	global $youtube_apiKey;
	if (strlen($playlist) == 0) { return 0; }

	$url = "https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&maxResults=50&playlistId=" . $playlist . "&key=" . $youtube_apiKey;
	$text = url_get_contents($url);
	if (substr($text, 0, 1) != "{") {
		return 0;
	}
	//$text = utf8_decode($text);
	$content = json_decode($text, JSON_ASSOC);
	$results = array();
	foreach ($content['items'] as $item) {
		$results[] = array(
			"id" => $item['snippet']['resourceId']['videoId'],
			"publishedOn" => $item['snippet']['publishedAt'],
			"title" => $item['snippet']['title'],
			"description" => strip_tags($item['snippet']['description']),
			"thumbnail" => $item['snippet']['thumbnails']['standard']['url']
		);
	}
	return $results;
}

/*function user_imap_email($userObject, $to, $subject, $message, $headers ) {
	util_setIV($userObject['emailIMAPPasswordIV']);
	$usePassword = util_decrypt($userObject['emailIMAPPassword'], $userObject['emailIMAPPasswordSalt']);
	$imap_connection = imap_open("{" . $userObject['emailIMAPServer'] . ":993/imap/ssl/novalidate-cert}INBOX", $userObject['email'], $usePassword);
	if ($imap_connection === FALSE) {
	    return mail($to, $subject, $message, $headers);
	}
	$sent = imap_mail($to, $subject, $message, $headers);
	imap_close($imap_connection, CL_EXPUNGE);

	util_getIV(true);

	if (util_isLocalhost()) {
		$sent = false;
	}

	return $sent;
}*/

function get_impress_email_template($include_footer = false, $include_trackingpixel = false) {
	global $impresslist_company_name;
	global $impresslist_company_addressLine;
	global $impresslist_company_emailAddress;
	global $impresslist_company_twitter;
	global $impresslist_company_facebook;

	ob_start();
	include_once($_SERVER['DOCUMENT_ROOT'] . "/data/email-templates/impress.phtml");
	$message = ob_get_contents();
	ob_end_clean();

	$message = str_replace("{{HTTP_HOST}}", $_SERVER['HTTP_HOST'], $message);
	$message = str_replace("{{COMPANY_NAME}}", $impresslist_company_name, $message);
	$message = str_replace("{{COMPANY_ADDRESS_LINE}}", $impresslist_company_addressLine, $message);
	$message = str_replace("{{COMPANY_EMAIL}}", $impresslist_company_emailAddress, $message);
	$message = str_replace("{{COMPANY_TWITTER}}", $impresslist_company_twitter, $message);
	$message = str_replace("{{COMPANY_FACEBOOK}}", $impresslist_company_facebook, $message);

	$message = str_replace("{{INCLUDE_FOOTER_BEGIN}}", ($include_footer)?"":"<!--", $message);
	$message = str_replace("{{INCLUDE_FOOTER_END}}", ($include_footer)?"":"-->", $message);

	$message = str_replace("{{INCLUDE_TRACKINGPIXEL_BEGIN}}", ($include_trackingpixel)?"":"<!--", $message);
	$message = str_replace("{{INCLUDE_TRACKINGPIXEL_END}}", ($include_trackingpixel)?"":"-->", $message);

	return $message;
}
function email_new_coverage($fromName, $url, $time) {
	return email_new_youtube_coverage($fromName, $url, $time);
}
function email_new_youtube_coverage($youtuberName, $url, $time) {
	global $impresslist_emailAddress;
	global $db;

	// email queue.
	$reply_to = "no-reply" . substr($impresslist_emailAddress, strpos($impresslist_emailAddress, "@"));
	$headers  = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1" . "\r\n";
	$headers .= "From: impress[] <{$impresslist_emailAddress}>" . "\r\n";
	$headers .= "Reply-To: {$reply_to}" . "\r\n";
	$headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

	$message = get_impress_email_template(false, false);

	$contents = "	<h2 style='margin-top: 0; margin-bottom: 15px;'>Coverage Alert!</h2>
					<p style='margin-top: 0; margin-bottom: 15px;'>
						Wahoo! You have new coverage from <strong>" . $youtuberName . "</strong>.
					</p>
					<p style='margin-top: 0; margin-bottom: 15px;'>
						Check it out <a style='color: #2f7f6f;text-decoration:none' href='" . $url . "'>here</a>, and be sure to send them a message of thanks!
					</p>";
	$message = str_replace("{{EMAIL_CONTENTS_HTML}}", $contents, $message);

	// Get users who have coverage alerts on.
	$users = $db->query("SELECT * FROM user WHERE coverageNotifications = 1;");
	for($i = 0; $i < count($users); $i++)
	{
		// insert into e-mail queue.
		$stmt = $db->prepare("INSERT INTO emailqueue (id, subject, to_address, headers, message, `timestamp`, sent)
											VALUES (NULL, :subject, :to_address, :headers, :message, :utime, 0 ); ");
		$stmt->bindValue(":subject", "impress[] - Coverage Alert!", Database::VARTYPE_STRING);
		$stmt->bindValue(":to_address", $users[$i]['email'], Database::VARTYPE_STRING);
		$stmt->bindValue(":headers", $headers, Database::VARTYPE_STRING);
		$stmt->bindValue(":message", $message, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$stmt->execute();
	}

	return true;
}


// email
// http://www.finalwebsites.com/forums/topic/php-e-mail-attachment-script
function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message) {
    $file = $path.$filename;
    $file_size = filesize($file);
    $handle = fopen($file, "r");
    $content = fread($handle, $file_size);
    fclose($handle);
    $content = chunk_split(base64_encode($content));
    $uid = md5(uniqid(time()));
    $name = basename($file);
    $header = "From: ".$from_name." <".$from_mail.">\r\n";
    $header .= "Reply-To: ".$replyto."\r\n";
    $header .= "MIME-Version: 1.0\r\n";
    $header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
    $header .= "This is a multi-part message in MIME format.\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
    $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $header .= $message."\r\n\r\n";
    $header .= "--".$uid."\r\n";
    $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
    $header .= "Content-Transfer-Encoding: base64\r\n";
    $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
    $header .= $content."\r\n\r\n";
    $header .= "--".$uid."--";
    if (mail($mailto, $subject, "", $header)) {
        echo "mail send ... OK"; // or use booleans here
    } else {
        echo "mail send ... ERROR!<br/>";
        //if ($impresslist_verbose) {
        	//echo "<b>Headers:</b><br/>" . $header  . "<br/><br/>";
        	echo "<b>Mailto:</b><br/>" . $mailto  . "<br/><br/>";
        	echo "<b>Subject:</b><br/>" . $subject  . "<br/><br/>";
        	print_r(error_get_last());
        //}
    }
}

function discord_webhook($data, $decode = false) {
	global $discord_enabled;
	global $discord_webhookId;
	global $discord_webhookToken;
	$discord_webhookUrl = "https://discordapp.com/api/webhooks/{$discord_webhookId}/{$discord_webhookToken}";

	if (!$discord_enabled) { return ""; }

	$fields;
	if ($decode) {
		$fields = array("payload_json" => urlencode(json_encode($data)));
	} else {
		$fields = $data;
	}
	foreach($fields as $key => $value) {
		$fields_string .= $key . '=' . $value . '&';
	}
	rtrim($fields_string, '&');

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $discord_webhookUrl);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	//curl_setopt($ch, CURLOPT_MUTE, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);
	return $result;
}
function discord_coverageAlert($fromName, $coverageTitle, $url) {
	$data = array(
		"content" => "*{$fromName}* - {$coverageTitle}: \n{$url}"
	);
	return discord_webhook($data);
}

function slack_incomingWebhook($data) {
	global $slack_enabled;
	global $slack_apiUrl;

	if (!$slack_enabled) { return ""; }

	$fields = array("payload" => urlencode(json_encode($data)));
	foreach($fields as $key => $value) {
		$fields_string .= $key . '=' . $value . '&';
	}
	rtrim($fields_string, '&');

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $slack_apiUrl);
	curl_setopt($ch, CURLOPT_POST, count($fields));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLOPT_MUTE, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	$result = curl_exec($ch);
	return $result;
}

function slack_coverageAlert($fromName, $coverageTitle, $url) {
	global $slack_enabled;
	if (!$slack_enabled) { return ""; }

	$data = array(
		"text" => "*Coverage Alert!*",
		"username" => "impress[]",
		"icon_emoji" => "thumbsup",
		"unfurl_links" => true,
		"attachments" => array(
			array(
				"fallback" => $url,
				//"pretext" => "*Coverage Alert!*",
				"color" => "#eeeeee",
				"fields" => array(
					array(
						"title" => $fromName,
						"value" => "<" . $url . "|" . $coverageTitle . ">",
						"short" => false
					)
				)
			)
		)
	);
	return slack_incomingWebhook($data);
}
function slack_jobsChanged($arr, $fromUser) {
	global $slack_enabled;
	if (!$slack_enabled) { return ""; }

	$str = implode("\n", $arr);

	$data = array(
		"text" => "*Jobs Updated!*",
		"username" => "impress[]",
		"icon_emoji" => "thumbsup",
		"unfurl_links" => true,
		"attachments" => array(
			array(
				"fallback" => $url,
				//"pretext" => "*Coverage Alert!*",
				"color" => "#eeeeee",
				"fields" => array(
					array(
						"title" => "by " . $fromUser,
						"value" => $str,
						"short" => false
					)
				)
			)
		)
	);
	return slack_incomingWebhook($data);
}

function user_updateActivity($user_id) {
	// Update current user time.
	global $db;
	$stmt = $db->prepare("UPDATE user SET lastactivity = :lastactivity WHERE id = :id;");
	$stmt->bindValue(":lastactivity", time(), Database::VARTYPE_INTEGER);
	$stmt->bindValue(":id", $user_id, Database::VARTYPE_INTEGER);
	$stmt->execute();
}


function listcountries() {
	$c = array(
		"Unknown" => "",
		"Afghanistan" => "af",
		"Albania" => "al",
		"Algeria" => "dz",
		"American Samoa" => "as",
		"Andorra" => "ad",
		"Angola" => "ao",
		"Anguilla" => "ai",
		"Antarctica" => "aq",
		"Antigua and Barbuda" => "ag",
		"Argentina" => "ar",
		"Armenia" => "am",
		"Aruba" => "aw",
		"Australia" => "au",
		"Austria" => "at",
		"Azerbaijan" => "az",
		"Bahamas" => "bs",
		"Bahrain" => "bh",
		"Bangladesh" => "bd",
		"Barbados" => "bb",
		"Belarus" => "by",
		"Belgium" => "be",
		"Belize" => "bz",
		"Benin" => "bj",
		"Bermuda" => "bm",
		"Bhutan" => "bt",
		"Bolivia" => "bo",
		"Bonaire" => "bq",
		"Bosnia and Herzegovina" => "ba",
		"Botswana" => "bw",
		"Bouvet Island" => "bv",
		"Brazil" => "br",
		"British Indian Ocean Territory" => "io",
		"Brunei Darussalam" => "bn",
		"Bulgaria" => "bg",
		"Burkina Faso" => "bf",
		"Burundi" => "bi",
		"Cambodia" => "kh",
		"Cameroon" => "cm",
		"Canada" => "ca",
		"Cape Verde" => "cv",
		"Cayman Islands" => "ky",
		"Central African Republic" => "cf",
		"Chad" => "td",
		"Chile" => "cl",
		"China" => "cn",
		"Christmas Island" => "cx",
		"Cocos (Keeling) Islands" => "cc",
		"Colombia" => "co",
		"Comoros" => "km",
		"Congo" => "cg",
		"Democratic Republic of the Congo" => "cd",
		"Cook Islands" => "ck",
		"Costa Rica" => "cr",
		"Croatia" => "hr",
		"Cuba" => "cu",
		"Curacao" => "cw",
		"Cyprus" => "cy",
		"Czech Republic" => "cz",
		"Cote d'Ivoire" => "ci",
		"Denmark" => "dk",
		"Djibouti" => "dj",
		"Dominica" => "dm",
		"Dominican Republic" => "do",
		"Ecuador" => "ec",
		"Egypt" => "Eeg",
		"El Salvador" => "sv",
		"Equatorial Guinea" => "gq",
		"Eritrea" => "er",
		"Estonia" => "ee",
		"Ethiopia" => "et",
		"Falkland Islands (Malvinas)" => "fk",
		"Faroe Islands" => "fo",
		"Fiji" => "fj",
		"Finland" => "fi",
		"France" => "fr",
		"French Guiana" => "gf",
		"French Polynesia" => "pf",
		"French Southern Territories" => "tf",
		"Gabon" => "ga",
		"Gambia" => "gm",
		"Georgia" => "ge",
		"Germany" => "de",
		"Ghana" => "gh",
		"Gibraltar" => "gi",
		"Greece" => "gr",
		"Greenland" => "gl",
		"Grenada" => "gr",
		"Guadeloupe" => "gp",
		"Guam" => "gu",
		"Guatemala" => "gt",
		"Guernsey" => "gg",
		"Guinea" => "gn",
		"Guinea-Bissau" => "gw",
		"Guyana" => "gy",
		"Haiti" => "ht",
		"Heard Island and McDonald Islands" => "hm",
		"Holy See (Vatican City State)" => "va",
		"Honduras" => "hn",
		"Hong Kong" => "hk",
		"Hungary" => "hu",
		"Iceland" => "is",
		"India" => "in",
		"Indonesia" => "id",
		"Iran" => "ir",
		"Iraq" => "iq",
		"Ireland" => "ie",
		"Isle of Man" => "im",
		"Israel" => "il",
		"Italy" => "it",
		"Jamaica" => "am",
		"Japan" => "jp",
		"Jersey" => "je",
		"Jordan" => "jo",
		"Kazakhstan" => "kz",
		"Kenya" => "ke",
		"Kiribati" => "ki",
		"North Korea" => "kp",
		"South Korea" => "kr",
		"Kuwait" => "kw",
		"Kyrgyzstan" => "kg",
		"Lao People's Democratic Republic" => "la",
		"Latvia" => "lv",
		"Lebanon" => "lb",
		"Lesotho" => "ls",
		"Liberia" => "lr",
		"Libya" => "ly",
		"Liechtenstein" => "li",
		"Lithuania" => "lt",
		"Luxembourg" => "lu",
		"Macao" => "mo",
		"Macedonia" => "mk",
		"Madagascar" => "mg",
		"Malawi" => "mw",
		"Malaysia" => "my",
		"Maldives" => "mv",
		"Mali" => "ml",
		"Malta" => "mt",
		"Marshall Islands" => "mh",
		"Martinique" => "mq",
		"Mauritania" => "mr",
		"Mauritius" => "mu",
		"Mayotte" => "yt",
		"Mexico" => "mx",
		"Micronesia" => "fm",
		"Moldova" => "md",
		"Monaco" => "mc",
		"Mongolia" => "mn",
		"Montenegro" => "me",
		"Montserrat	MS" => "ms",
		"Morocco" => "ma",
		"Mozambique" => "mz",
		"Myanmar" => "mm",
		"Namibia" => "na",
		"Nauru" => "nr",
		"Nepal" => "np",
		"Netherlands" => "nl",
		"New Caledonia" => "nc",
		"New Zealand" => "nz",
		"Nicaragua" => "ni",
		"Niger" => "ne",
		"Nigeria" => "ng",
		"Niue" => "nu",
		"Norfolk Island" => "nf",
		"Northern Mariana Islands" => "mp",
		"Norway" => "no",
		"Oman" => "om",
		"Pakistan" => "pk",
		"Palau" => "pw",
		"Palestine, State of" => "ps",
		"Panama" => "pa",
		"Papua New Guinea" => "pg",
		"Paraguay" => "py",
		"Peru" => "pe",
		"Philippines" => "ph",
		"Pitcairn" => "pn",
		"Poland" => "pl",
		"Portugal" => "pt",
		"Puerto Rico" => "pr",
		"Qatar" => "qa",
		"Romania" => "ro",
		"Russia" => "ru",
		"Rwanda" => "rw",
		"Reunion" => "re",
		"Saint Barthelemy" => "bl",
		"Saint Helena" => "sh",
		"Saint Kitts and Nevis" => "kn",
		"Saint Lucia" => "lc",
		"Saint Martin (French part)" => "mf",
		"Saint Pierre and Miquelon" => "pm",
		"Saint Vincent and the Grenadines" => "vc",
		"Samoa" => "ws",
		"San Marino" => "sm",
		"Sao Tome and Principe" => "st",
		"Saudi Arabia" => "sa",
		"Senegal" => "sn",
		"Serbia" => "rs",
		"Seychelles" => "sc",
		"Sierra Leone" => "sl",
		"Singapore" => "sg",
		"Sint Maarten (Dutch part)" => "sx",
		"Slovakia" => "sk",
		"Slovenia" => "si",
		"Solomon Islands" => "sb",
		"Somalia" => "so",
		"South Africa" => "za",
		"South Georgia and the South Sandwich Islands" => "gs",
		"South Sudan" => "Sss",
		"Spain" => "es",
		"Sri Lanka" => "lk",
		"Sudan" => "sd",
		"Suriname" => "sr",
		"Svalbard and Jan Mayen" => "sj",
		"Swaziland" => "sz",
		"Sweden" => "se",
		"Switzerland" => "ch",
		"Syrian Arab Republic" => "sy",
		"Taiwan" => "tw",
		"Tajikistan" => "tj",
		"United Republic of Tanzania" => "tz",
		"Thailand" => "th",
		"Timor-Leste" => "tl",
		"Togo" => "tg",
		"Tokelau" => "tk",
		"Tonga" => "to",
		"Trinidad and Tobago" => "tt",
		"Tunisia" => "tn",
		"Turkey" => "tr",
		"Turkmenistan" => "tm",
		"Turks and Caicos Island" => "tc",
		"Tuvalu" => "tv",
		"Uganda" => "ug",
		"Ukraine" => "ua",
		"United Arab Emirates" => "ae",
		"United Kingdom" => "gb",
		"United States" => "us",
		"United States Minor Outlying Islands" => "um",
		"Uruguay" => "uy",
		"Uzbekistan" => "uz",
		"Vanuatu" => "vu",
		"Venezuela" => "ve",
		"Viet Nam" => "vn",
		"British Virgin Islands" => "vg",
		"US Virgin Islands" => "vi",
		"Wallis and Futuna" => "wf",
		"Western Sahara" => "eh",
		"Yemen" => "ye",
		"Zambia" => "zm",
		"Zimbabwe" => "zw"
	);
	return $c;
}
function util_listNintendoRegions() {
	return array('us' => 'Americas',
		'eu' => 'Europe &amp; South Africa',
		'au' => 'Australia &amp; New Zealand',
		'jp' => 'Japan'
	);
}
function util_findNintendoRegionForCountry($country) {
	$countries = listcountries();
	$nintendoRegions = array_keys(util_listNintendoRegions());

	$americas = array( "us", "um", "mx", "br", "ar", "cl", "ca", "ve", "pe");
	$europe = array("gb", "fr", "it", "de", "pl","pt","es","si","sk","se", "ch", "za","ad","at","bg","hr","cy","cz","dk","ee","fi","gi","gr","gg","hu","is","ie","im","je","lv","li","lt","lu","mk","mt","nl","no", "ua", "ru", "ro");
	$ausnz = array( "au", "nz" );

	if (in_array($country, $americas)) {
		return 'us';
	}
	if (in_array($country, $europe)) {
		return 'eu';
	}
	if (in_array($country, $ausnz)) {
		return 'au';
	}
	if ($country == "jp") {
		return 'jp';
	}
	// Unknown
	return '';
}

function urlformat($str) {
	$str = strtolower($str);
	$str = str_replace(" ", "-", $str);
	$str = str_replace("&amp;", "and", $str);
	$str = str_replace("&", "and", $str);
	$str = str_replace(array("[", "]", "!", '"', "'", ".", ",", ":", "(", ")"), "", $str);
	return $str;
}

?>
