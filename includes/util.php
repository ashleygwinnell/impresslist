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



// ----------------------------------------------------------------------------
// Twitter
// ----------------------------------------------------------------------------
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/twitter/twitteroauth.php");
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
  	$connection = new TwitterOAuth($twitter_consumerKey, $twitter_consumerSecret, $oauth_token, $oauth_token_secret);
  	return $connection;
} 
 
function twitter_countFollowers($username) 
{
	global $twitter_oauthToken;
	global $twitter_oauthSecret;
	if (strlen($username) == 0) { return 0; }

	$twitter_connection = twitter_getConnectionWithAccessToken($twitter_oauthToken, $twitter_oauthSecret);
	$twitter_content = $twitter_connection->get("users/lookup.json?callback=?&screen_name=" . $username . "&count=1");
	if (isset($twitter_content->errors)) { return 0; }
	
	//echo json_encode($twitter_content);
	return $twitter_content[0]->followers_count;
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

function youtube_v3_getInformation($channel) {
	global $youtube_apiKey;
	if (strlen($channel) == 0) { return 0; }

	$url = "https://www.googleapis.com/youtube/v3/channels?forUsername=" .$channel . "&key=" . $youtube_apiKey . "&part=contentDetails,snippet,statistics&maxResults=50";
	$text = file_get_contents($url);
	if (substr($text, 0, 1) != "{") { 
		return 0;
	}
	$content = json_decode($text, JSON_ASSOC);
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
	$text = file_get_contents($url);
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
        echo "mail send ... ERROR!";
    }
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




?>