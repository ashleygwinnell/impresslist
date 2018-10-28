<?php

$require_config = true;

function api_error($message) {
	$error = new stdClass();
	$error->success = false;
	$error->message = $message;
	return $error;
}

function api_checkRequiredGETFields($fields, &$result) {
	for($i = 0; $i < count($fields); $i++) {
		if (!isset($_GET[$fields[$i]])) {
			$result = api_error($fields[$i] . " was not set");
			return true;
		}
	}
	return false;
}
function api_checkRequiredGETFieldsWithTypes($fields, &$result) {
	for($i = 0; $i < count($fields); $i++) {
		if (!isset($_GET[$fields[$i]['name']])) {
			$result = api_error($fields[$i]['name'] . " was not set");
			return true;
		} else {
			// it's set, check the type.
			$type = $fields[$i]['type'];
			if ($type == 'email') {
				$email = $_GET[$fields[$i]['name']];
				if (strlen($email) > 0 && !util_isEmail($email)) {
					$result = api_error($fields[$i]['name'] . " is not a valid email.");
					return true;
				}
			} else if ($type == 'integer') {
				if (!util_isInteger($_GET[$fields[$i]['name']])) {
					$result = api_error($fields[$i]['name'] . " is not a valid integer.");
					return true;
				}
			} else if ($type == 'priority') {
				$val = $_GET[$fields[$i]['name']];
				if (!util_isInteger($val)) {
					$result = api_error($fields[$i]['name'] . " is not a valid integer.");
					return true;
				} else if ($val != 0 && $val != 1 && $val != 2 && $val != 3) {
					$result = api_error($fields[$i]['name'] . " is not a valid priority integer.");
					return true;
				}
			} else if ($type == 'alphanumeric') {
				$temp = $_GET[$fields[$i]['name']];
				if (!util_isAlphaNumeric($temp)) {
					$result = api_error($fields[$i]['name'] . " is not a valid alphanumeric string. -- " . $temp);
					return true;
				}
			} else if ($type == 'alphanumerichyphens') {
				$temp = $_GET[$fields[$i]['name']];
				if (!util_isAlphaNumericWithExtras($temp, array("-"), 255, 0)) {
					$result = api_error($fields[$i]['name'] . " is not a valid alphanumerichyphens string. -- " . $temp);
					return true;
				}
			} else if ($type == 'alphanumerichyphensnewlines') {
				$temp = $_GET[$fields[$i]['name']];
				if (!util_isAlphaNumericWithExtras($temp, array("-", "\n"), 4096*2*2, 0)) {
					$result = api_error($fields[$i]['name'] . " is not a valid alphanumerichyphensnewlines string. -- " . $temp);
					return true;
				}
			} else if ($type == 'alphanumericspaces') {
				$temp = $_GET[$fields[$i]['name']]; // str_replace("%20", " ", $_GET[$fields[$i]['name']]);
				//if (!util_isAlphaNumericWithSpaces($temp)) {
				if (!util_isAlphaNumericWithSpaces($fields[$i]['name'], $temp, 255, 0)) {
					$result = api_error($fields[$i]['name'] . " is not a valid alphanumeric (with spaces) string. -- " . $temp);
					return true;
				}
			} else if ($type == 'platform') {
				$temp = $_GET[$fields[$i]['name']];
				if (!util_isValidPlatformForProjectKeys($temp)) {
					$result = api_error($fields[$i]['name'] . " is not a valid platform. " . implode(",",util_getValidPlatformsForProjectKeys()) . " only right now. -- " . $temp);
					return true;
				}
			} else if ($type == 'url') {

				$http = substr($_GET[$fields[$i]['name']], 0, 7);
				$https = substr($_GET[$fields[$i]['name']], 0, 8);

				if ($http == "http://") { }
				else if ($https == "https://") { }
				else if ($https == "") { }
				else {
					$result = api_error($fields[$i]['name'] . " should begin with http:// or https:// ");
					return true;
				}

				//$temp = strip_tags($_GET[$fields[$i]['name']]);
				//return true;
			} else if ($type == 'textarea') {
				$temp = strip_tags($_GET[$fields[$i]['name']]);
			} else if ($type == 'boolean' || $type == "bool") {
				$val = $_GET[$fields[$i]['name']];
				if ($val != true && $val != false) {
					$result = api_error($fields[$i]['name'] . " should have been a boolean value. ");
					return true;
				}
			}

		}
	}
	return false;
}

function api_result($r) {
	$var = json_encode($r, true);
	if ($var === FALSE) {
		$lasterr = json_last_error();
		echo json_encode(api_error("utf8 error -- could not encode data... " . $lasterr));
		print_r($r);
	} else {
		echo $var;
	}
	die();
}

$result = null;
if (!isset($_GET['endpoint'])) {
	$result = api_error("endpoint was not set.");
} else {

	$endpoints = array(

		// Install
		"/install/database/",
		"/install/administrator/",
		"/install/cronjobs/",
		"/install/system-email/",
		"/install/twitter-settings/",
		"/install/youtube-settings/",
		"/install/complete/",

		// People
		"/person/list/",
		"/person/add/",
		"/person/save/",
		"/person/remove/",
		"/person/add-publication/",
		"/person/save-publication/",
		"/person/remove-publication/",
		"/person/add-youtube-channel/",
		"/person/remove-youtube-channel/",
		"/person/add-twitchchannel/",
		"/person/remove-twitchchannel/",
		"/person/set-priority/",
		"/person/set-assignment/",

		// Publications
		"/publication/list/",
		"/publication/add/",
		"/publication/set-priority/",
		"/publication/save/",
		"/publication/remove/",

		// Youtubers
		"/youtuber/list/",
		"/youtuber/search-youtube/",
		"/youtuber/add/",
		"/youtuber/save/",
		"/youtuber/set-priority/",
		"/youtuber/remove/",

		// Twitch Channels
		"/twitchchannel/list/",
		"/twitchchannel/add/",
		"/twitchchannel/save/",
		"/twitchchannel/set-priority/",
		"/twitchchannel/remove/",

		// Projects
		"/project/add/",

		// Admin
		"/admin/sql-query/",
		"/admin/user/add/",
		"/admin/user/save/",
		"/admin/user/remove/",
		"/admin/user/change-password/",
		//"/admin/user/change-project/",
		"/backup/",
		"/backup-sql/",

		// User settings
		"/user/change-imap-settings/",
		"/user/change-password/",
		"/user/change-project/",

		"/job/list/",
		"/job/save-all/",

		"/person-publication/list/",
		"/person-youtube-channel/list/",
		"/person-twitchchannel/list/",
		"/email/list/",
		"/email/remove/",

		// Import tool/s
		"/import/",

		// Mailouts
		"/mailout/simple/list/",
		"/mailout/simple/add/",
		"/mailout/simple/save/",
		"/mailout/simple/send/",
		"/mailout/simple/cancel/",
		"/mailout/simple/remove/",

		// Key management
		"/keys/list/",
		"/keys/add/",
		"/keys/pop/",

		// Coverage management
		"/coverage/",
		"/coverage/publication/add/",
		"/coverage/publication/save/",
		"/coverage/publication/remove/",
		"/coverage/youtuber/add/",
		"/coverage/youtuber/save/",
		"/coverage/youtuber/remove/",
		"/coverage/twitchchannel/add/",
		"/coverage/twitchchannel/save/",
		"/coverage/twitchchannel/remove/",

		// Watched Game management.
		"/watchedgame/list/",
		"/watchedgame/add/",
		"/watchedgame/save/",
		"/watchedgame/remove/",

		// Social
		"/social/timeline/",
		"/social/timeline/item/add/",
		"/social/timeline/item/save/",
		"/social/timeline/item/remove/",
		"/social/timeline/item/add-retweets/",

		// Social Uploads
		"/social/uploads/list/",
		"/social/uploads/add/",
		"/social/uploads/remove/",

		"/social/account/twitter/list/",
		"/social/account/twitter/add/",
		"/social/account/twitter/remove/",

		"/social/account/facebook/list/",
		"/social/account/facebook/add/",
		"/social/account/facebook/add-callback/",
		"/social/account/facebook/remove/",
		"/social/account/facebook-page/query/",
		"/social/account/facebook-page/add/",
		"/social/account/facebook-page/remove/",
		"/social/account/facebook-page/list/",

		"/social/account/twitch/add-callback/",

		// Chat
		"/chat/online-users/",
		"/chat/lines/",
		"/chat/send/",

		// Test...
		"/test/test/",
		"/test/phpinfo/"
	);
	$endpoint = $_GET['endpoint'];
	if (!in_array($endpoint, $endpoints)) {
		$result = api_error("API endpoint " . $endpoint . " does not exist.");
	} else {

		if ($endpoint == "/test/test/")
		{
			$require_login = false;
			include_once('init.php');

			$var = twitter_countFollowers("forcehabit");
			print_r($var);

			//$acc = db_singleOAuthTwitterByHandle($db, "ashleygwinnell");
			//$tweet = twitter_postStatus($acc['oauth_key'], $acc['oauth_secret'], "This is another test of the Twitter API. Boop.");
			//print_r($tweet);

			//$r = twitter_helpConfiguration();
			//$r = db_singleOAuthTwitter($db, 123);
			//var_dump($r);
			//print_r($r);

			//$acc = db_singleOAuthTwitterByHandle($db, "ashleygwinnell");
			//$r = twitter_getUserInfoById($acc['oauth_key'], $acc['oauth_secret'], "28089893");
			//echo $r->profile_image_url;
			//print_r($r);

			//$acc = db_singleOAuthTwitterByHandle($db, "ashleygwinnell");
			//$r = twitter_retweetStatus($acc['oauth_key'], $acc['oauth_secret'], "684422428987146245");
			//print_r($r);

			/*$images = array(
				"images/uploads/CHUD.png",
				"images/uploads/CANARD.png",
				"images/uploads/OMR.png",
				"images/uploads/SHAKEYJAKE.png"
			);
			$acc = db_singleOAuthTwitterByHandle($db, "forcehabit");
			$tweet = twitter_postStatusWithImage($acc['oauth_key'], $acc['oauth_secret'], "Hey, do you remember when we & @cuckooclockwork did these? ;D #friendshipclub", $images);
			print_r($tweet);*/

			$result = new stdClass();
			$result->success = true;

		}
		else if ($endpoint == "/test/phpinfo/")
		{
			$require_login = false;
			include_once('init.php');

			phpinfo();

			$result = new stdClass();
			$result->success = true;
		}
		else if ($endpoint == "/install/database/")
		{
			$required_fields = array(
				array('name' => 'mysql_host', 	 	'type' => 'textarea'),
				array('name' => 'mysql_username', 	'type' => 'textarea'),
				array('name' => 'mysql_password', 	'type' => 'textarea'),
				array('name' => 'mysql_database',	'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$data = $_GET;
				if (count($data) > 5) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Too many parameters passed.";
					api_result($result);
					die();
				}

				$require_login = false;
				$require_config = false;
				include_once('includes/database.class.php');
				include_once('includes/cache.class.php');

				$impresslist_mysqlServer 		= $data['mysql_host'];
				$impresslist_mysqlUsername		= $data['mysql_username'];
				$impresslist_mysqlPassword		= $data['mysql_password'];
				$impresslist_mysqlDatabaseName	= $data['mysql_database'];
				$impresslist_databaseType 		= Database::TYPE_MYSQL;
				include_once('includes/database.php');

				try {
					$db = Database::getInstance();

					session_start();
					$_SESSION['install'] = [];
					$_SESSION['install'] = array_merge([], $data);

					db_install($db);

					$result = new stdClass();
					$result->success = true;
					api_result($result);
					die();

				} catch (Exception $e) {
					$result = new stdClass();
					$result->success = false;
					$result->message = $e->getMessage();
					api_result($result);
					die();
				}
			}

		}
		else if ($endpoint == "/install/administrator/")
		{
			include_once("includes/util.php");
			$required_fields = array(
				array('name' => 'forename','type' => 'textarea'),
				array('name' => 'surname', 	'type' => 'textarea'),
				array('name' => 'email', 	'type' => 'email'),
				array('name' => 'password',	'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$data = $_GET;

				$require_login = false;
				$require_config = false;
				include_once('includes/database.class.php');

				session_start();
				$impresslist_mysqlServer 		= $_SESSION['install']['mysql_host'];
				$impresslist_mysqlUsername		= $_SESSION['install']['mysql_username'];
				$impresslist_mysqlPassword		= $_SESSION['install']['mysql_password'];
				$impresslist_mysqlDatabaseName	= $_SESSION['install']['mysql_database'];
				$impresslist_databaseType 		= Database::TYPE_MYSQL;

				$db = Database::getInstance();

				// Check there's no user already.
				$users = $db->query("SELECT * FROM user LIMIT 1;");
				if (count($users) > 0) {
					// impress[] already has an administrator. Let's just update them.
					$stmt = $db->prepare("UPDATE user SET forename = :forename, surname = :surname, email = :email, password = :password WHERE id = 1; ");
					$stmt->bindValue(":forename", 		$data['forename'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":surname", 		$data['surname'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":email", 			$data['email'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":password", 		md5($data['password']), Database::VARTYPE_STRING);
					$stmt->execute();

					$result = new stdClass();
					$result->success = true;
					api_result($result);
				}
				else {
					$stmt = $db->prepare("INSERT IGNORE INTO game (id, name, iconurl, keywords) VALUES ( :id, :name, :iconurl, :keywords ); ");
					$stmt->bindValue(":id", 			1, 				Database::VARTYPE_INTEGER);
					$stmt->bindValue(":name", 			'Project', 		Database::VARTYPE_STRING);
					$stmt->bindValue(":iconurl", 		'', 			Database::VARTYPE_STRING);
					$stmt->bindValue(":keywords", 		'project', 		Database::VARTYPE_STRING);
					$stmt->execute();

					$stmt = $db->prepare("INSERT IGNORE INTO user (id, forename, surname, email, password, currentGame, admin, color, lastactivity) VALUES ( :id, :forename, :surname, :email, :password, :currentGame, :admin, :color, :lastactivity ); ");
					$stmt->bindValue(":id", 			1, 						Database::VARTYPE_INTEGER);
					$stmt->bindValue(":forename", 		$data['forename'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":surname", 		$data['surname'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":email", 			$data['email'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":password", 		md5($data['password']), Database::VARTYPE_STRING);
					$stmt->bindValue(":currentGame", 	1, 						Database::VARTYPE_INTEGER);
					$stmt->bindValue(":admin", 			1, 						Database::VARTYPE_INTEGER);
					$stmt->bindValue(":color", 			'#000000', 				Database::VARTYPE_STRING);
					$stmt->bindValue(":lastactivity", 	time(), 				Database::VARTYPE_INTEGER);
					$stmt->execute();

					$result = new stdClass();
					$result->success = true;
					api_result($result);
				}
			}

		}
		else if ($endpoint == "/install/cronjobs/") {
			$require_config = false;
			$require_login  = false;
			include_once('init.php');

			$perms = fileperms($uploadsDir);
			$octalPerms = substr(sprintf('%o', $perms), -4);

			if ($octalPerms != '0755') {
				$result = new stdClass();
				$result->success = false;
				$result->message = $uploadsDir . ' permissions was not set correctly. It was set to ' . $octalPerms . '.';
				api_result($result);
			}

			$perms = fileperms('includes/config/');
			$octalPerms = substr(sprintf('%o', $perms), -4);

			if ($octalPerms != '0755') {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'includes/config/ permissions was not set correctly. It was set to ' . $octalPerms . '.';
				api_result($result);
			}

			$result = new stdClass();
			$result->success = true;
			api_result($result);
		}
		else if ($endpoint == "/install/system-email/") {
			include_once("includes/util.php");
			$required_fields = array(
				array('name' => 'email_host',		'type' => 'textarea'),
				array('name' => 'email_address', 	'type' => 'email'),
				array('name' => 'email_password', 	'type' => 'textarea'),
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				session_start();

				$data = $_GET;
				if (count($data) > 4) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Too many parameters passed.";
					api_result($result);
					die();
				}

				$require_login = false;
				$require_config = false;
				include_once('includes/database.class.php');

				$impresslist_emailIMAPHost = $data['email_host'];
				$impresslist_emailAddress = $data['email_address'];
				$impresslist_emailPassword = $data['email_password'];

				$imap_connection = @imap_open("{" . $impresslist_emailIMAPHost . ":993/imap/ssl/novalidate-cert}INBOX", $impresslist_emailAddress, $impresslist_emailPassword);

				if ( !$imap_connection ) {
					$result = new stdClass();
					$result->success = false;
					$result->message = imap_last_error();
					$result->message .= ' You should try running the installer on the live server.';
					api_result($result);
				} else {
					$_SESSION['install'] = array_merge($_SESSION['install'], $data);

					$result = new stdClass();
					$result->success = true;
					api_result($result);
				}

			}
		}
		else if ($endpoint == "/install/twitter-settings/") {
			$require_config = false;
			$require_login  = false;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'twitter_consumer_key',		'type' => 'textarea'),
				array('name' => 'twitter_consumer_secret', 	'type' => 'textarea'),
				array('name' => 'twitter_oauth_token', 		'type' => 'textarea'),
				array('name' => 'twitter_oauth_secret', 	'type' => 'textarea'),
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				session_start();

				$data = $_GET;
				if (count($data) > 5) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Too many parameters passed.";
					api_result($result);
					die();
				}

				$twitter_consumerKey = $data['twitter_consumer_key'];
  				$twitter_consumerSecret = $data['twitter_consumer_secret'];
				$twitter_oauthToken = $data['twitter_oauth_token'];
				$twitter_oauthSecret = $data['twitter_oauth_secret'];

				$twitterConfig = twitter_helpConfiguration();
			//	print_r($twitterConfig);

				//echo twitter_countFollowers("forcehabit");
				if (!$twitterConfig || ( $twitterConfig && isset($twitterConfig->errors))) {
					$errorMessage = ($twitterConfig->errors[0]->message)?:'';
					$result = new stdClass();
					$result->success = false;
					$result->message = 'Could not set up Twitter API. ' . $errorMessage;
					api_result($result);
				} else {
					$result = new stdClass();
					$result->success = true;
					$result->configuration = $twitterConfig;

					$_SESSION['install'] = array_merge($_SESSION['install'], $data);
					$_SESSION['install']['twitter_configuration'] = $twitterConfig;
				}

			}
		}
		else if ($endpoint == "/install/youtube-settings/")
		{
			$require_config = false;
			$require_login  = false;
			include_once("init.php");
			$required_fields = array(
				array('name' => 'youtube_api_key',		'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				session_start();

				$data = $_GET;

				$youtube_apiKey = $data['youtube_api_key'];
				$info = youtube_v3_getInformation('forcehabit');
				if (isset($info['id']) && isset($info['name'])) {
					$result = new stdClass();
					$result->success = true;

					$_SESSION['install'] = array_merge($_SESSION['install'], ['youtube_api_key' => $youtube_apiKey ]);
					unset($_SESSION['install']['endpoint']);



				} else {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'Could not set up YouTube API. Please check the API key that you entered.';
				}

			}
		}
		else if ($endpoint == "/install/complete/")
		{
			$require_config = false;
			$require_login  = false;
			include_once("init.php");

			$newConfigFile = 'includes/config/config.php';
			if (file_exists( $newConfigFile )) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Config file already exists. How are you running this installation?!';
			}
			else {

				include_once('includes/database.class.php');

				session_start();

				$impresslist_mysqlServer 		= $_SESSION['install']['mysql_host'];
				$impresslist_mysqlUsername		= $_SESSION['install']['mysql_username'];
				$impresslist_mysqlPassword		= $_SESSION['install']['mysql_password'];
				$impresslist_mysqlDatabaseName	= $_SESSION['install']['mysql_database'];
				$impresslist_databaseType 		= Database::TYPE_MYSQL;

				$db = Database::getInstance();

				twitter_helpConfigurationSave($_SESSION['install']['twitter_configuration']);
				$db->exec("UPDATE settings SET `value` = '" . $_SESSION['install']['twitter_consumer_key'] . "'    	WHERE `key` = 'twitter_consumerKey' ; ");
				$db->exec("UPDATE settings SET `value` = '" . $_SESSION['install']['twitter_consumer_secret'] . "' 	WHERE `key` = 'twitter_consumerSecret' ; ");
				$db->exec("UPDATE settings SET `value` = '" . $_SESSION['install']['twitter_oauth_token'] . "' 		WHERE `key` = 'twitter_oauthToken' ; ");
				$db->exec("UPDATE settings SET `value` = '" . $_SESSION['install']['twitter_oauth_secret'] . "' 	WHERE `key` = 'twitter_oauthSecret' ; ");

				$db->exec("UPDATE settings SET `value` = '" . $_SESSION['install']['youtube_api_key'] . "' 	WHERE `key` = 'youtube_apiKey' ; ");

				$template = file_get_contents("includes/config/config.template.php");

				$template = str_replace('{email_host}', 	$_SESSION['install']['email_host'], 	$template);
				$template = str_replace('{email_address}', 	$_SESSION['install']['email_address'], 	$template);
				$template = str_replace('{email_password}', $_SESSION['install']['email_password'], $template);

				$template = str_replace('{mysql_host}', 	$_SESSION['install']['mysql_host'], 	$template);
				$template = str_replace('{mysql_username}', $_SESSION['install']['mysql_username'], $template);
				$template = str_replace('{mysql_password}', $_SESSION['install']['mysql_password'], $template);
				$template = str_replace('{mysql_database}', $_SESSION['install']['mysql_database'], $template);

				$result = file_put_contents($newConfigFile, $template);
				if ($result === FALSE) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'Could not create config file.';
					api_result($result);
				}

				unset($_SESSION['install']);
				$result = new stdClass();
				$result->success = true;


			}

		}
		else if ($endpoint == "/social/account/facebook-page/query/")
		{
			$require_login = true;
			include_once('init.php');

			$fb = new Facebook\Facebook([
				'app_id' => $facebook_appId,
				'app_secret' => $facebook_appSecret,
				'default_graph_version' => $facebook_apiVersion,
			]);

			//echo $user_id;
			$fbuser = db_singleOAuthFacebookByUserId($db, $user_id);
			if (is_null($fbuser)) {
				$result = new stdClass();
				$result->success = false;
				$result->message = "You cannot link a Page until linking your personal account.";
				api_result($result);
			}
			//print_r($fbuser);
			$accessToken = $fbuser['facebook_accessToken'];

			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $fb->get('/' . $fbuser['facebook_id'] . '/accounts', $accessToken);
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Graph returned an error: ' . $e->getMessage();
				api_result($result);
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Facebook SDK returned an error: ' . $e->getMessage();
				api_result($result);
				exit;
			}



			$validPages = array();
			$bodyJson = $response->getBody();
			//echo $bodyJson;
			$body = json_decode($bodyJson, true);
			for($i = 0; $i < count($body['data']); $i++) {
				$item = $body['data'][$i];
				//echo $item['name'] . "<br/>";
				//echo $item['access_token'];
				//echo $item['id'];



				$foundBasicAdmin = false;
				$foundCreateContent = false;
				$foundModerateContent = false;
				for ($j = 0; $j < count($item['perms']); $j++) {
					if ($item['perms'][$j] == "BASIC_ADMIN") { $foundBasicAdmin = true; }
					if ($item['perms'][$j] == "CREATE_CONTENT") { $foundCreateContent = true; }
					if ($item['perms'][$j] == "MODERATE_CONTENT") { $foundModerateContent = true; }
				}
				if ($foundBasicAdmin && $foundCreateContent && $foundModerateContent) {
					unset($item['perms']);
					$item['image'] = "http://graph.facebook.com/" . $item['id'] . "/picture?type=square";
					$validPages[] = $item;
				}
			}

			$result = new stdClass;
			$result->success = true;
			$result->facebookpages = $validPages;

		}
		else if ($endpoint == "/social/account/facebook-page/list/")
		{
			$require_login = true;
			include_once('init.php');

			$results = $db->query("SELECT * FROM oauth_facebookpage WHERE removed = 0 ORDER BY id ASC;");
			usort($results, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->facebookpages = $results;
		}
		else if ($endpoint == "/social/account/facebook-page/add/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'page_id', 	 		'type' => 'integer'),
				array('name' => 'page_name', 		'type' => 'textarea'),
				array('name' => 'page_accessToken',	'type' => 'alphanumeric')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// TODO:
				// don't blindly trust the client.

				$image_url = "http://graph.facebook.com/" . $_GET['page_id'] . "/picture?type=square";

				$exists = db_singleOAuthFacebookPageByFBPId($db, $_GET['page_id']);
				if (!is_null($exists)) {

					$stmt = $db->prepare("UPDATE oauth_facebookpage SET page_name = :page_name, page_image = :page_image, page_accessToken = :page_accessToken, lastSync = :lastSync, removed = :removed WHERE page_id = :page_id; ");

					$stmt->bindValue(":page_name", 			$_GET['page_name'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":page_image", 		$image_url, 				Database::VARTYPE_STRING);
					$stmt->bindValue(":page_accessToken",  	$_GET['page_accessToken'], 	Database::VARTYPE_STRING);
					$stmt->bindValue(":page_id", 			$user['id'], 				Database::VARTYPE_STRING);
					$stmt->bindValue(":lastSync", 			time(), 					Database::VARTYPE_INTEGER);
					$stmt->bindValue(":removed", 			0, 							Database::VARTYPE_INTEGER);
					$stmt->execute();

					$result = new stdClass();
					$result->success = true;
					$result->facebookpage = $exists;
					$result->updated = true;

				} else {

					$stmt = $db->prepare("INSERT INTO oauth_facebookpage (id, page_id, page_name, page_image, page_accessToken, lastSync, removed)
															VALUES ( NULL, :page_id, :page_name, :page_image, :page_accessToken, :lastSync, :removed);");
					$stmt->bindValue(":page_id", 			$_GET['page_id'], 			Database::VARTYPE_STRING);
					$stmt->bindValue(":page_name", 			$_GET['page_name'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":page_image", 		$image_url, 				Database::VARTYPE_STRING);
					$stmt->bindValue(":page_accessToken",  	$_GET['page_accessToken'], 	Database::VARTYPE_STRING);
					$stmt->bindValue(":lastSync",  			time(), 					Database::VARTYPE_INTEGER);
					$stmt->bindValue(":removed", 			0, 							Database::VARTYPE_INTEGER);
					$stmt->execute();

					$facebookPageId = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->facebookpage = db_singleOAuthFacebookPageById( $db, $facebookPageId );
				}
			}
		}
		else if ($endpoint == "/social/account/facebook-page/remove/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array( array('name' => 'id', 'type' => 'alphanumeric') );
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" UPDATE oauth_facebookpage SET removed = :removed WHERE id = :id; ");
				$stmt->bindValue(":removed", 	1, 				Database::VARTYPE_INTEGER);
				$stmt->bindValue(":id", 		$_GET['id'], 	Database::VARTYPE_STRING);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/social/account/facebook/list/")
		{
			$require_login = true;
			include_once('init.php');

			$results = $db->query("SELECT * FROM oauth_facebookacc WHERE removed = 0 ORDER BY id ASC;");
			usort($results, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->facebookaccs = $results;
		}
		else if ($endpoint == "/social/account/facebook/add/")
		{
			$require_login = true;
			include_once('init.php');

			$fb = new Facebook\Facebook([
				'app_id' => $facebook_appId,
				'app_secret' => $facebook_appSecret,
				'default_graph_version' => $facebook_apiVersion,
			]);

			$helper = $fb->getRedirectLoginHelper();

			$permissions = ['email', "manage_pages", "publish_pages", "publish_actions"]; // Optional permissions
			$loginUrl = $helper->getLoginUrl("http://".$_SERVER['HTTP_HOST'] . '/api.php?endpoint=/social/account/facebook/add-callback/', $permissions);

			// echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';
			header("Location: " . $loginUrl);
			die();

		}
		else if ($endpoint == "/social/account/facebook/remove/")
		{
			$require_login = true;
			include_once('init.php');

			$result = new stdClass();
			$result->success = false;
			$result->message = "Removing Facebook accounts is not yet implemented.";
		}
		else if ($endpoint == "/social/account/facebook/add-callback/") {

			$require_login = true;
			include_once('init.php');

			$fb = new Facebook\Facebook([
				'app_id' => $facebook_appId,
				'app_secret' => $facebook_appSecret,
				'default_graph_version' => $facebook_apiVersion,
			]);

			$helper = $fb->getRedirectLoginHelper();

			$accessToken = "";
			try {
			  	$accessToken = $helper->getAccessToken();
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
			  	// When Graph returns an error
			  	$result = new stdClass();
				$result->success = false;
				$result->message = 'Graph returned an error: ' . $e->getMessage();
			  	api_result($result);

			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				// When validation fails or other local issues
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Facebook SDK returned an error: ' . $e->getMessage();
			  	api_result($result);
			}

			if (! isset($accessToken)) {
			  if ($helper->getError()) {
			    header('HTTP/1.0 401 Unauthorized');
			    echo "Error: " . $helper->getError() . "\n";
			    echo "Error Code: " . $helper->getErrorCode() . "\n";
			    echo "Error Reason: " . $helper->getErrorReason() . "\n";
			    echo "Error Description: " . $helper->getErrorDescription() . "\n";
			  } else {
			    header('HTTP/1.0 400 Bad Request');
			    echo 'Bad request';
			  }
			  exit;
			}

			// Logged in
			//echo '<h3>Access Token</h3>';
			//var_dump($accessToken->getValue());

			// The OAuth 2.0 client handler helps us manage access tokens
			$oAuth2Client = $fb->getOAuth2Client();

			// Get the access token metadata from /debug_token
			$tokenMetadata = $oAuth2Client->debugToken($accessToken);
			//echo '<h3>Metadata</h3>';
			//var_dump($tokenMetadata);

			// Validation (these will throw FacebookSDKException's when they fail)
			$tokenMetadata->validateAppId($facebook_appId);

			// If you know the user ID this access token belongs to, you can validate it here
			//$tokenMetadata->validateUserId('123');
			//$tokenMetadata->validateExpiration();

			if (! $accessToken->isLongLived()) {
				// Exchanges a short-lived access token for a long-lived one
				try {
					$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
				} catch (Facebook\Exceptions\FacebookSDKException $e) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Error getting long-lived access token: " . $helper->getMessage();
					api_result($result);
				}

				//echo '<h3>Long-lived</h3>';
				//var_dump($accessToken->getValue());
			}

			//$_SESSION['fb_access_token'] = (string) $accessToken;

			//


			// User is logged in with a long-lived access token.
			// You can redirect them to a members-only page.
			//header('Location: https://example.com/members.php');

			try {
				// Returns a `Facebook\FacebookResponse` object
				$response = $fb->get('/me?fields=id,name', $accessToken->getValue());
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Graph returned an error: ' . $e->getMessage();
				api_result($result);
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'Facebook SDK returned an error: ' . $e->getMessage();
				api_result($result);
				exit;
			}

			$user = $response->getGraphUser();
			$image_url = "http://graph.facebook.com/" . $user['id'] . "/picture?type=square";

			// This facebook account is in the db already. cool. update it.
			$exists = db_singleOAuthFacebookByFBId($db, $user['id']);
			if (!is_null($exists)) {

				$stmt = $db->prepare("UPDATE oauth_facebookacc SET facebook_image = :facebook_image, facebook_accessToken = :facebook_accessToken, removed = :removed WHERE facebook_id = :facebook_id; ");

				$stmt->bindValue(":facebook_image", 		$image_url, 				Database::VARTYPE_STRING);
				$stmt->bindValue(":facebook_accessToken",  	$accessToken->getValue(), 	Database::VARTYPE_STRING);
				$stmt->bindValue(":facebook_id", 			$user['id'], 				Database::VARTYPE_STRING);
				$stmt->bindValue(":removed", 				0, 							Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = false;
				$result->message = "Facebook account already exists and was updated. You should now close this window and refresh impress[].";
				api_result($result);
				//echo "<script type='text/javascript'>window.close();</script>";

			}

			// If this user has a Facebook linked already (don't let them add another. note that to get to here the facebook id would be different, so it would be multiple accounts).
			$exists2 = db_singleOAuthFacebookByUserId($db, $user_id);
			if ($exists2) {
				$result = new stdClass();
				$result->success = false;
				$result->message = "You have already linked a Facebook account so cannot add another. You should now close this window and refresh impress[].";
				api_result($result);
			}


			$stmt = $db->prepare("INSERT INTO oauth_facebookacc (id, user, facebook_id, facebook_name, facebook_image, facebook_accessToken, removed)
													VALUES ( NULL, :user, :facebook_id, :facebook_name, :facebook_image, :facebook_accessToken, :removed);
								");
			$stmt->bindValue(":user", 					$user_id, 					Database::VARTYPE_STRING);
			$stmt->bindValue(":facebook_id", 			$user['id'], 				Database::VARTYPE_STRING);
			$stmt->bindValue(":facebook_name", 			$user['name'], 				Database::VARTYPE_STRING);
			$stmt->bindValue(":facebook_image", 		$image_url, 				Database::VARTYPE_STRING);
			$stmt->bindValue(":facebook_accessToken",  	$accessToken->getValue(), 	Database::VARTYPE_STRING);
			$stmt->bindValue(":removed", 				0, 							Database::VARTYPE_INTEGER);
			$stmt->execute();

			$facebookAccId = $db->lastInsertRowID();

			$result = new stdClass();
			$result->success = true;
			$result->facebookacc = db_singleOAuthFacebookByFBId( $db, $user['id'] );
			$result->message = "You should now close this window and refresh impress[].";

			/*
			echo 'ID: ' . $user['id'] . "<br/>";
			echo 'Name: ' . $user['name'] . "<br/>";
			//print_r($user);
			echo "<br/>";


			echo $image_url;
			//$accessToken = $helper->getAccessToken();

			try {
				$response2 = $fb->get("/".$user['id']."/accounts", $accessToken);
				print_r($response2);
			} catch(Facebook\Exceptions\FacebookResponseException $e) {
				echo 'Graph returned an error: ' . $e->getMessage();
				exit;
			} catch(Facebook\Exceptions\FacebookSDKException $e) {
				echo 'Facebook SDK returned an error: ' . $e->getMessage();
				exit;
			}*/


			// http://localhost/api.php?endpoint=%2Fsocial%2Faccount%2Ffacebook%2Fadd-callback%2F
			//  &code=AQAUO_7pi431flBKMMqtZx2eBZLEMWrjiOPp4QT5pEVSI5tMmRyiPxRkjz4JKiOBYX7ErcgcCIffSrCAoYvDoE0R0r07psP4zimEclKaPBGWlTE1AxDvprCBFMWQGz7_YpIbDfliN6cI6KPC871fvgrCTjf3R5J4RrtDwD66ET_dNHNKDHcsKo8HVTWUMWu90aYKArXusG5cQhDTMohqeK-peEZnYjoiTPIFyJzntxH--IFC2VB6bJRpdP_62L5gJND7xFAVIcutxqt3JqDvRW_ZnqDdH47DhNrrtxgpyqN5M_pymeFPNe0RQIjNmRRojDA
			//  &state=5d02cfffba84ffa2d620b5c430607af9#_=_
		}

		else if ($endpoint == "/social/timeline/")
		{
			$require_login = true;
			include_once('init.php');

			$includeSent = ($_GET['sent'] == "true")?1:0;
			$sentSQL = "";
			if (isset($_GET['sent'])) {
				$sentSQL = " AND sent = {$includeSent} ";
			}

			$results = $db->query("SELECT * FROM socialqueue WHERE `timestamp` > " . (time()-86400). " AND removed = 0 " . $sentSQL . " ORDER BY `timestamp` ASC;");

			$result = new stdClass();
			$result->success = true;
			$result->timeline = $results;

			for($i = 0; $i < count($result->timeline); $i++) {
				$result->timeline[$i]['typedata'] = json_decode($result->timeline[$i]['typedata']);
			}
		}
		else if ($endpoint == "/social/timeline/item/add/")
		{
			$require_login = true;
			include_once('init.php');

			$stmt = $db->prepare("INSERT INTO socialqueue (id, type, typedata, user_id, `timestamp`, ready, sent, removed)
													VALUES ( NULL, :type, :typedata, :user_id, :ts, :ready, :sent, :removed);
								");
			$stmt->bindValue(":type", 		'blank', 	Database::VARTYPE_STRING);
			$stmt->bindValue(":typedata", 	"{}", 		Database::VARTYPE_STRING);
			$stmt->bindValue(":user_id", 	$user_id, 	Database::VARTYPE_INTEGER);
			$stmt->bindValue(":ts", 		0, 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":ready", 		0, 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":sent", 		0, 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":removed", 	0, 			Database::VARTYPE_INTEGER);

			$stmt->execute();

			$itemId = $db->lastInsertRowID();

			$result = new stdClass();
			$result->success = true;
			$result->socialTimelineItem = db_singleSocialQueueItem( $db, $itemId );
			$result->socialTimelineItem['typedata'] = json_decode($result->socialTimelineItem['typedata']);
		}
		else if ($endpoint == "/social/timeline/item/add-retweets/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'id', 		 'type' => 'integer'),
				array('name' => 'accounts',	 'type' => 'textarea'),
				array('name' => 'timesep', 	 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$tweet = db_singleSocialQueueItem($db, $_GET['id']);

				if ($tweet == null) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Tweet could not be found.";
				} else if ($tweet['type'] != 'tweet') {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Cannot add retweets to a non-tweet item.";
				} else {
					$accounts = explode(",", $_GET['accounts']);


					$correct = true;
					for($i = 0; $i < count($accounts); $i++)
					{
						$r = db_singleOAuthTwitter($db, $accounts[$i]);
						if (is_null($r)) {
							$correct = false;
							break;
						}

					}
					if (strlen($_GET['accounts']) == 0 || count($accounts) == 0) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "No accounts were passed.";
					} else if (!$correct) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "A twitter account that was passed was not found.";
					} else {

						$result = new stdClass();
						$result->success = true;
						$result->socialTimelineItems = array();

						for($i = 0; $i < count($accounts); $i++)
						{
							$retweet_data = array(
								"tweet" => $_GET['id'],
								"account" => $accounts[$i]
							);
							$time = $tweet['timestamp'] + (($i+1) * $_GET['timesep']);

							$stmt = $db->prepare("INSERT INTO socialqueue (id, type, typedata, user_id, `timestamp`, ready, sent, removed)
																	VALUES ( NULL, :type, :typedata, :user_id, :ts, :ready, :sent, :removed);
												");
							$stmt->bindValue(":type", 		'retweet', 	Database::VARTYPE_STRING);
							$stmt->bindValue(":typedata", 	json_encode($retweet_data), Database::VARTYPE_STRING);
							$stmt->bindValue(":user_id", 	$user_id, 	Database::VARTYPE_INTEGER);
							$stmt->bindValue(":ts", 		$time, 		Database::VARTYPE_INTEGER);
							$stmt->bindValue(":ready", 		1, 			Database::VARTYPE_INTEGER);
							$stmt->bindValue(":sent", 		0, 			Database::VARTYPE_INTEGER);
							$stmt->bindValue(":removed", 	0, 			Database::VARTYPE_INTEGER);
							$stmt->execute();

							$itemId = $db->lastInsertRowID();

							$item = db_singleSocialQueueItem($db, $itemId);
							$item['typedata'] = json_decode($item['typedata']);
							$result->socialTimelineItems[] = $item;
						}
					}
				}

			}



		}
		else if ($endpoint == "/social/timeline/item/save/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'id', 		 'type' => 'integer'),
				array('name' => 'type', 	 'type' => 'alphanumeric'),
				array('name' => 'data', 	 'type' => 'textarea'),
				array('name' => 'ready', 	 'type' => 'boolean'),
				array('name' => 'timestamp', 'type' => 'integer'),
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// TODO: validate ID is a valid item.
				// TODO: if we're changing the timestamp.
				// 		 we should error if there are retweets that take place afterwards...

				$valid = true;

				if ($_GET['type'] == "retweet")
				{
					// make sure the timestamp is AFTER the tweet takes place.
					$d = json_decode($_GET['data'], true);
					if (!is_numeric($d['account'])) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "Please select a Twitter account from the list.";
						api_result($result);
					}

					$tweet = db_singleSocialQueueItem($db, $d['tweet']);
					if ($tweet['timestamp'] > $_GET['timestamp']) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "Cannot schedule a retweet to happen before the tweet takes place.";
					}

					// check there's not a retweet by this account already.
					//$rs = $db->query("SELECT * FROM socialqueue WHERE id = " . $id . " AND typedata = LIMIT 1;");
					//return $rs[0];
					$stmt1 = $db->prepare("SELECT * FROM socialqueue WHERE type = 'retweet' AND typedata = :typedata AND id != :id AND removed = 0;");
					$stmt1->bindValue(":typedata", $_GET['data'], Database::VARTYPE_STRING);
					$stmt1->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$existingretweet = $stmt1->query();
					//print_r($existingretweet);
					if (count($existingretweet) > 0) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "A retweet for this account & tweet is already scheduled.";
					}


					$tweetdata = json_decode($tweet['typedata'], true);
					//print_r($tweetdata);
					if ($d['account'] == $tweetdata['account']) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "The original tweet is from this account... so it cannot be scheduled to retweet!";
					}


				} else if ($_GET['type'] == "fbshare")
				{
					// make sure the timestamp is AFTER the tweet takes place.
					$d = json_decode($_GET['data'], true);
					if (!is_numeric($d['account'])) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "Please select a Facebook Page from the list.";
						api_result($result);
					}

					$tweet = db_singleSocialQueueItem($db, $d['post']);
					if ($tweet['timestamp'] > $_GET['timestamp']) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "Cannot schedule a share to happen before the post takes place.";
					}

					// check there's not a retweet by this account already.
					//$rs = $db->query("SELECT * FROM socialqueue WHERE id = " . $id . " AND typedata = LIMIT 1;");
					//return $rs[0];
					$stmt1 = $db->prepare("SELECT * FROM socialqueue WHERE type = 'fbshare' AND typedata = :typedata AND id != :id AND removed = 0;");
					$stmt1->bindValue(":typedata", $_GET['data'], Database::VARTYPE_STRING);
					$stmt1->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$existingretweet = $stmt1->query();
					//print_r($existingretweet);
					if (count($existingretweet) > 0) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "A share for this page & post is already scheduled.";
					}


					$tweetdata = json_decode($tweet['typedata'], true);
					//print_r($tweetdata);
					if ($d['account'] == $tweetdata['account']) {
						$valid = false;
						$result = new stdClass();
						$result->success = false;
						$result->message = "The original post is from this page... so it cannot be scheduled to share!";
					}


				}

				if (!$valid) {

				} else {

					$ready = ($_GET['ready'] == "true")?1:0;
					$stmt = $db->prepare("UPDATE socialqueue SET `type` = :type, `typedata` = :typedata, `ready` = :ready, `timestamp` = :timestamp WHERE id = :id ;");
					$stmt->bindValue(":type", $_GET['type'], Database::VARTYPE_STRING);
					$stmt->bindValue(":typedata", $_GET['data'], Database::VARTYPE_STRING);
					$stmt->bindValue(":timestamp", $_GET['timestamp'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":ready", $ready, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();

					$result = new stdClass();
					$result->success = true;
					$result->socialTimelineItem = db_singleSocialQueueItem($db, $_GET['id']);
					$result->socialTimelineItem['typedata'] = json_decode($result->socialTimelineItem['typedata']);
				}
			}
		}
		else if ($endpoint == "/social/timeline/item/remove/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'id', 		 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// TODO: validate ID is a valid item.

				$stmt = $db->prepare("UPDATE socialqueue SET `removed` = :removed WHERE id = :id ;");
				$stmt->bindValue(":removed", 1, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}

		else if ($endpoint == "/social/uploads/list/")
		{
			$require_login = true;
			include_once('init.php');

			$result = new stdClass();
			$result->success = true;
			$result->uploads = array();

			$uploads = scandir($uploadsDir);

			// sort by upload date
			function sortbyuploaddate($a, $b) {
				global $uploadsDir;
				return filemtime($uploadsDir.$a) > filemtime($uploadsDir.$b);
			}
			usort($uploads, "sortbyuploaddate");

			foreach ($uploads as $upload) {

				$ext = substr($upload, strlen($upload)-3, 3);
				if ($ext == "png" || $ext == "jpg" || $ext == "gif") {
					$result->uploads[] = array(
						"name" => $upload,
						"fullname" => $uploadsDir . $upload,
						"type" => "image/{$ext}",
						"size" => filesize($uploadsDir.$upload)
					);
				}
			}



		}
		else if ($endpoint == "/social/uploads/add/")
		{
			$require_login = true;
			include_once('init.php');

			if(isset($_FILES["file"]["type"]))
			{
				$validextensions = array("jpeg", "jpg", "png", "gif");
				$temporary = explode(".", $_FILES["file"]["name"]);
				$file_extension = end($temporary);
				if ((
						($_FILES["file"]["type"] == "image/png") ||
						($_FILES["file"]["type"] == "image/jpg") ||
						($_FILES["file"]["type"] == "image/jpeg") ||
						($_FILES["file"]["type"] == "image/gif")
					) && ($_FILES["file"]["size"] < 1024 * 1024 * 5) // Approx. 5mb files can be uploaded.
					  && in_array($file_extension, $validextensions))
				{
					if ($_FILES["file"]["error"] > 0)
					{
						$result = new stdClass();
						$result->success = false;
						$result->message = "Return Code: " . $_FILES["file"]["error"];
					}
					else
					{
						if (file_exists($uploadsDir . $_FILES["file"]["name"])) {
							$result = new stdClass();
							$result->success = false;
							$result->message = $_FILES["file"]["name"] . " already exists.";
						}
						else
						{
							$sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable
							$targetPath = $uploadsDir.$_FILES['file']['name']; // Target path where file is to be stored
							move_uploaded_file($sourcePath, $targetPath) ; // Moving Uploaded file

							$result = new stdClass();
							$result->success = true;
							$result->upload = array(
								"name" => $_FILES["file"]["name"],
								"fullname" => $uploadsDir . $_FILES["file"]["name"],
								"type" => $_FILES["file"]["type"],
								"size" => $_FILES["file"]["size"]
							);
						}
					}
				}
				else {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Invalid file size or file type.";
				}
			}

		}
		else if ($endpoint == "/social/uploads/remove/")
		{
			$require_login = true;
			include_once('init.php');

			if (!isset($_GET['name'])) {
				$result = new stdClass();
				$result->success = false;
				$result->message = "API requires 'name' field to remove social uploads. ";
			} else {
				$name = $_GET['name'];

				$exists = file_exists($uploadsDir . $name);
				if (!$exists) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Social Upload {$name} does not exist. ";
				} else {
					$r = unlink($uploadsDir . $name);
					if (!$r) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "Could not remove {$name} file.";
					} else {
						$result = new stdClass();
						$result->success = true;
					}
				}
			}

		}
		else if ($endpoint == "/social/account/twitter/list/")
		{
			$require_login = true;
			include_once('init.php');

			$results = $db->query("SELECT * FROM oauth_twitteracc WHERE removed = 0 ORDER BY id ASC;");
			usort($results, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->twitteraccs = $results;
		}
		else if ($endpoint == "/social/account/twitter/add/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'request_token', 		'type' => 'alphanumericunderscores'),
				array('name' => 'request_token_secret', 'type' => 'alphanumericunderscores'),
				array('name' => 'pin', 			 		'type' => 'alphanumeric')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$pin = $_GET['pin'];
				$connection = new Abraham\TwitterOAuth\TwitterOAuth($twitter_consumerKey, $twitter_consumerSecret, $_GET['request_token'], $_GET['request_token_secret']);
				$token = $connection->getAccessToken($pin);

				if (!isset($token['screen_name'])) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Could not add Twitter Account (for an unknown reason).";
				} else {

					// Make sure this isn't in tehre already!
					$existing_result = db_singleOAuthTwitterByHandle($db, $token['screen_name']);
					if ($existing_result == null) {

						$tw_user = twitter_getUserInfoById($token['oauth_token'], $token['oauth_token_secret'], $token['user_id']);

						$stmt = $db->prepare("INSERT INTO oauth_twitteracc (id, twitter_id, twitter_name, twitter_handle, twitter_image, oauth_key, oauth_secret, removed)
																VALUES ( NULL, :twitter_id, :twitter_name, :twitter_handle, :twitter_image, :oauth_key, :oauth_secret, :removed);
											");
						$stmt->bindValue(":twitter_id", 	$token['user_id'], 				Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_name", 	$token['screen_name'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_handle", $token['screen_name'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_image",  $tw_user->profile_image_url, 	Database::VARTYPE_STRING);
						$stmt->bindValue(":oauth_key", 		$token['oauth_token'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":oauth_secret", 	$token['oauth_token_secret'], 	Database::VARTYPE_STRING);
						$stmt->bindValue(":removed", 		0, 								Database::VARTYPE_INTEGER);
						$stmt->execute();

						//print_r($token);

						$twitterAccId = $db->lastInsertRowID();

						$result = new stdClass();
						$result->success = true;
						$result->twitteracc = db_singleOAuthTwitter( $db, $twitterAccId );
					} else {
						//$result = new stdClass();
						//$result->success = false;
						//$result->message = "Twitter Account already exists.";

						$tw_user = twitter_getUserInfoById($existing_result['oauth_key'], $existing_result['oauth_secret'], $existing_result['twitter_id']);

						// Update user
						$stmt = $db->prepare("UPDATE oauth_twitteracc
												SET twitter_id = :twitter_id,
													twitter_name = :twitter_name,
													twitter_handle = :twitter_handle,
													twitter_image = :twitter_image,
													removed = :removed
												WHERE id = :id;
											");
						$stmt->bindValue(":twitter_id", 	$token['user_id'], 				Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_name", 	$token['screen_name'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_handle", $token['screen_name'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":twitter_image",  $tw_user->profile_image_url, 	Database::VARTYPE_STRING);
						$stmt->bindValue(":oauth_key", 		$token['oauth_token'], 			Database::VARTYPE_STRING);
						$stmt->bindValue(":oauth_secret", 	$token['oauth_token_secret'], 	Database::VARTYPE_STRING);
						$stmt->bindValue(":removed", 		0, 								Database::VARTYPE_INTEGER);
						$stmt->bindValue(":id", 			$existing_result['id'], 		Database::VARTYPE_INTEGER);
						$stmt->execute();

						$result = new stdClass();
						$result->success = true;
						$result->twitteracc = db_singleOAuthTwitter( $db, $existing_result['id'] );
						$result->updated = true;

					}
				}
			}

		}
		else if ($endpoint == "/social/account/twitter/remove/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array( array('name' => 'id', 		'type' => 'alphanumeric') );
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" UPDATE oauth_twitteracc SET removed = :removed WHERE id = :id; ");
				$stmt->bindValue(":removed", 	1, 				Database::VARTYPE_INTEGER);
				$stmt->bindValue(":id", 		$_GET['id'], 	Database::VARTYPE_STRING);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/backup/")
		{
			$require_login = true;
			include_once("init.php");

			/*$contents = file_get_contents('database.sql');
			$r = file_put_contents("backups/database-backup-" . time() . ".sql", $contents);
			if ($r === false) {
				api_error("Could not create local backup. You may need to manually chmod the backups directory.");
			} else {
				$result = new stdClass();
				$result->success = true;
			}*/
			$db->query("UPDATE settings SET `value` = " . time() . " WHERE `key` = 'manual_backup_lastbackedupon'; ");

			$filename = $impresslist_sqliteDatabaseName;
			$filename2 = $_SERVER['DOCUMENT_ROOT'] . "/" . $filename;
			$contents = file_get_contents($filename2);
			serve_file("impresslist-backup-" . date("c") . ".sql", $contents, "sql");

			header("Location: /");
			return;

		}
		else if ($endpoint == "/backup-sql/")
		{
			$require_login = true;
			include_once("init.php");

			$sql = $db->sql();

			serve_file("impresslist-backup-sql-" . date("c") . ".sql", $sql, "txt");
			die();


//			header("Location: /");
			//return;


		}



		else if ($endpoint == "/job/list/")
		{
			$require_login = true;
			include_once("init.php");

			$results = $db->query("SELECT * FROM settings WHERE `key` = 'todolist';");
			if (count($results) == 0) {
				$result = api_error("Jobs unavailable. Database error.");
	 	 	} else {
				$arr = explode("\n", $results[0]['value']);
				if (count($arr) == 0) {
					$arr = $results[0]['value'];
				}
				$result = new stdClass();
				$result->success = true;
				$result->jobs = $arr;
			}

		}
		else if ($endpoint == "/job/save-all/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'jobs', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$jobs = $_GET['jobs'];

				$stmt = $db->prepare("UPDATE settings SET `value` = :value WHERE `key` = :key;");
				$stmt->bindValue(":key", 'todolist', Database::VARTYPE_STRING);
				$stmt->bindValue(":value", $jobs, Database::VARTYPE_STRING);
				$stmt->execute();

				$arr = explode("\n", $jobs);
				if (count($arr) == 0) { $arr = $jobs; }

				$result = new stdClass();
				$result->success = true;
				$result->jobs = $arr;

				@slack_jobsChanged($arr, $user['forename'] . " " . $user['surname']);
			}



		}

		else if ($endpoint == "/mailout/simple/list/")
		{
			$require_login = true;
			include_once('init.php');

			$results = $db->query("SELECT * FROM emailcampaignsimple WHERE removed = 0 ORDER BY ready ASC, sent ASC, `timestamp` DESC;");

			$result = new stdClass();
			$result->success = true;
			$result->mailouts = $results;
		}
		else if ($endpoint == "/mailout/simple/add/")
		{
			$require_login = true;
			include_once('init.php');

			$stmt = $db->prepare("INSERT INTO emailcampaignsimple (id, name, subject, recipients, markdown, `timestamp`, user, ready, sent, removed)
														VALUES ( NULL, :name, :subject, :recipients, :markdown, :ts, :user, :ready, :sent, :removed);
								");
			$stmt->bindValue(":game_id", 	$user_currentGame, 	Database::VARTYPE_INTEGER);
			$stmt->bindValue(":name", 		'Unnamed Mailout', 	Database::VARTYPE_STRING);
			$stmt->bindValue(":subject", 	"Subject", 			Database::VARTYPE_STRING);
			$stmt->bindValue(":recipients", "[]", 				Database::VARTYPE_STRING);
			$stmt->bindValue(":markdown", 	"", 				Database::VARTYPE_STRING);
			$stmt->bindValue(":ts", 		0, 					Database::VARTYPE_INTEGER);
			$stmt->bindValue(":user", 		$user_id, 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":ready", 		0, 					Database::VARTYPE_INTEGER);
			$stmt->bindValue(":sent", 		0, 					Database::VARTYPE_INTEGER);
			$stmt->bindValue(":removed", 	0, 					Database::VARTYPE_INTEGER);
			$stmt->execute();

			$mailoutId = $db->lastInsertRowID();

			$result = new stdClass();
			$result->success = true;
			$result->mailout = db_singlemailoutsimple( $db, $mailoutId );
			// [{"type":"person","person_id":333,"sent":true,"read":false},{"type":"personPublication","personPublication_id":221,"sent":true,"read":false}]
		}
		else if ($endpoint == "/mailout/simple/save/")
		{
			$require_login = true;
			include_once('init.php');

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'name', 'type' => 'textarea'),
				array('name' => 'subject', 'type' => 'textarea'),
				array('name' => 'recipients', 'type' => 'textarea'),
				array('name' => 'markdown', 'type' => 'textarea'),
				array('name' => 'timestamp', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// validate person
				$person = $_GET['person'];
				if ($_GET['person'] != "" && !util_isInteger($person)) {
					$result = api_error("person was not an integer");
				} else {

					$json = json_decode(urldecode($_POST['recipients']), true);
					if ($json === NULL) {
						$result = api_error("Invalid json passed.");
					} else {

						// TODO:
						// Validate all people, personPublications, etc.
						// [{"type":"person","person_id":333,"sent":true,"read":false},{"type":"personPublication","personPublication_id":221,"sent":true,"read":false}]
						for($j = 0; $j < count($json); $j++) {
							if ($json[$j]['type'] == "person") {

							} else if ($json[$j]['type'] == "personPublication") {

							}
						}

						$stmt = $db->prepare("UPDATE emailcampaignsimple
										SET
											name = :name,
											subject = :subject,
											recipients = :recipients,
											markdown = :markdown,
											`timestamp` = :ts,
											user = :user,
											ready = :ready,
											sent = :sent,
											removed = :removed
										WHERE id = :id ;");
						$stmt->bindValue(":id", 		$_GET['id'], 		Database::VARTYPE_INTEGER);
						$stmt->bindValue(":name", 		$_GET['name'], 		Database::VARTYPE_STRING);
						$stmt->bindValue(":subject", 	$_GET['subject'], 	Database::VARTYPE_STRING);
						$stmt->bindValue(":recipients", urldecode($_POST['recipients']),Database::VARTYPE_STRING);
						$stmt->bindValue(":markdown", 	$_GET['markdown'], 	Database::VARTYPE_STRING);
						$stmt->bindValue(":ts", 		$_GET['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":user", 		$user_id, 			Database::VARTYPE_INTEGER);
						$stmt->bindValue(":ready", 		0, 					Database::VARTYPE_INTEGER);
						$stmt->bindValue(":sent", 		0, 					Database::VARTYPE_INTEGER);
						$stmt->bindValue(":removed", 	0, 					Database::VARTYPE_INTEGER);
						$stmt->execute();

						$result = new stdClass();
						$result->success = true;
						$result->mailout = db_singlemailoutsimple($db, $_GET['id'] );
					}
				}
			}


		}
		else if ($endpoint == "/mailout/simple/send/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// check smtp settings.
				if ($user['emailSMTPServer'] != '' &&
					$user['emailIMAPServer'] != '' &&
					$user['emailIMAPPassword'] != '' &&
					$user['emailIMAPPasswordSalt'] != '' &&
					$user['emailIMAPPasswordIV'] != '') {

					// if the mailout contains keys, we have to check that we have enough!
					$stmt = $db->prepare("SELECT * FROM emailcampaignsimple WHERE id = :id ;");
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$mailouts = $stmt->query();
					$mailout = $mailouts[0];
					$markdown = $mailout['markdown'];

					$doSend = true;

					$assertEnoughKeys = function($platform) use ($mailout, &$doSend, &$result) {
						$recipientsArray = json_decode($mailout['recipients'], true);
						$countRecipients = count($recipientsArray);
						// TODO: game_id should probably be part of the mailout data?!
						$keysArray = $db->query("SELECT *
											FROM game_key
											WHERE
												game = '" . $user_currentGame . "' AND
												platform = '" . $platform . " AND
												assigned = 0;");
						$countKeys = count($keysArray);

						if ($countKeys < $countRecipients) {
							$doSend = false;
							$numNewKeysNeeded = $countRecipients - $countKeys;
							$result = api_error("There are not enough ".$platform." keys in the system to allocate to this mailout. {$numNewKeysNeeded} more needed.");
						}
						return false;
					};

					$assertEnoughKeysCountExisting = function($platform) use ($mailout, &$doSend, &$result) {
						$recipientsArray = json_decode($mailout['recipients'], true);
						$newKeysNeeded = 0;
						for ($i = 0; $i < count($recipientsArray); $i++) {
							$contact = $recipientsArray[$i];
							if ($contact['type'] == "person") {
								$keysForContact = db_keysassignedtotype($db, $user_currentGame, 'steam', 'person', $contact['person_id']);
								if (count($keysForContact) == 0) {
									$newKeysNeeded++;
								}
							}
						}

						$keysArray = $db->query("SELECT *
											FROM game_key
											WHERE
												game = '" . $user_currentGame . "' AND
												platform = '" . $platform . "' AND
												assigned = 0;");
						$countKeys = count($keysArray);
						if ($countKeys < $newKeysNeeded) {
							$numNewKeysNeeded = $newKeysNeeded - $countKeys;
							$doSend = false;
							$result = api_error("There are not enough " . $platform . " keys in the system to allocate to this mailout. {$numNewKeysNeeded} more needed.");
						}
					};

					if (strpos($markdown, "{{steam_key}}") !== false) {
						$assertEnoughKeys("steam");
					}
					if (strpos($markdown, "{{switch_key}}") !== false) {
						$assertEnoughKeys("switch");
					}

					if (strpos($markdown, "{{steam_keys}}") !== false)
					{
						$assertEnoughKeysCountExisting("steam");
					}
					if (strpos($markdown, "{{switch_keys}}") !== false)
					{
						$assertEnoughKeysCountExisting("switch");
					}

					if ($doSend) {
						$stmt = $db->prepare(" UPDATE emailcampaignsimple SET ready = 1 WHERE id = :id ;");
						$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();

						$result = new stdClass();
						$result->success = true;
						$result->mailout = db_singlemailoutsimple($db, $_GET['id'] );
						$result->extras = $newKeysNeeded;
					}
				} else {
					$result = api_error('You need to configure your e-mail settings to perform a mailout.');
				}
			}
		}
		else if ($endpoint == "/mailout/simple/cancel/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$stmt = $db->prepare(" UPDATE emailcampaignsimple SET ready = 0 WHERE id = :id ;");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->mailout = db_singlemailoutsimple($db, $_GET['id'] );
			}
		}
		else if ($endpoint == "/mailout/simple/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$stmt = $db->prepare(" UPDATE emailcampaignsimple SET removed = 1 WHERE id = :id");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}



		else if ($endpoint == "/import/")
		{
			$require_login = true;
			include_once('init.php');

			$importData = $_GET['data'];
			$importType = $_GET['type'];
			$importOrder = $_GET['order'];
			$importOrderLength = count(explode(",", $importOrder));

			if (strlen($importData) == 0) {
				$result = new stdClass();
				$result->success = false;
				$result->message = "Import data was empty.";
			} else if ($importType != 'csv' && $importType != 'tsv') {
				$result = new stdClass();
				$result->success = false;
				$result->message = "Invalid import type. Please select CSV or TSV.";
			} else {

				$list = array();
				$lines = str_getcsv($importData, "\n");

				if (count($lines) == 0) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "No import lines found.";
				} else {

					$importerror = false;
					$importerrorint = -1;
					$importerrorcount = 0;

					$i = 1;
					$delimiter = ($importType == 'csv')?",":"\t";
					$enclosure = ($importType == 'csv')?'"':"";
					foreach($lines as $line) {
						if (strlen($line) == 0) { continue; }
						$csv = str_getcsv($line, $delimiter, $enclosure);

						//print_r($csv);
						if (count($csv) != $importOrderLength) {
							$importerror = true;
							$importerrorint = $i;
							$importerrorcount = count($csv);
							break;
						}

						$list[] = $csv;
						$i++;
					}

					if ($importerror) {
						$result = new stdClass();
						$result->success = false;
						$result->message = "Import line (" . ($importerrorint) . ") did not match length of order specified (" . $importerrorcount . " vs " . $importOrderLength . "). import type (" . $importType . ").";
					} else {

						$result = new stdClass();
						$result->success = true;
						//$result->data = $list;
						//$result->order = $importOrder;

						$order = explode(",", $importOrder);
						if (strpos($order[0], "person") === 0) {
							$result->type = "person";
						}
						else if (strpos($order[0], "publication") === 0) {
							$result->type = "publication";
						}
						else if (strpos($order[0], "twitchchannel") === 0) {
							$result->type = "twitchchannel";

							// Build data
							$map = [
								"twitchchannel_name" => "twitchUsername",
								"twitchchannel_email" => "email",
								"twitchchannel_notes" => "notes",
								"twitchchannel_twitter" => "twitter",
								"twitchchannel_unknown" => ""
							];

							$countImports = 0;
							$countSkips = 0;
							$skips = [];
							for($i = 0; $i < count($list); $i++) {


								$data = [
									'twitchId' => '',
									'twitchDescription' => '',
									'twitchBroadcasterType' => '',
									'twitchProfileImageUrl' => '',
									'twitchOfflineImageUrl' => '',
									'twitchUsername' => '',
									'name' => '',
									'email' => '',
									'priorities' => '',
									'subscribers' => '0',
									'views' => '0',
									'twitter' => '',
									'twitter_followers' => 0,
									'twitter_updatedon' => 0,
									'notes' => '',
									'lang' => '',
									'lastpostedon' => 0,
									'lastpostedon_updatedon' => 0,
									'removed' => 0,
									'lastscrapedon' => 0
								];

								$thisUsername = "";
								$intoHeaders = [];
								$intoKeys = [];
								$intoVals = [];
								$existing = false;
								for($j = 0; $j < count($order); $j++) {
									if ($map[$order[$j]] == "") {
										continue;
									}
									$h = $map[$order[$j]];
									$v = $list[$i][$j];


									//echo $v . "<br/>\n";
									if ($v == ""){
										continue;
									}

									$intoHeaders[] = $h;
									//$intoKeys[] = ":var" . count($intoKeys);
									$intoVals[] = $v;

									$data[$h] = $v;


									if ($h == "twitchUsername") {
										$thisUsername = $v;
										$existingItem = db_singletwitchchannelbyusername($db, $v);
										if ($existingItem != null) {
											$existing = true;
										}
									}
								}
								if ($existing) {
									$skips[] = $thisUsername;
									$countSkips++;
									continue;
								}

								//print_r($intoHeaders);
								//print_r($intoKeys);

								// " . implode(",", $intoHeaders) . "
								// " . implode(",", $intoKeys) . "

								$keys = array_keys($data);
								$vals = "";
								for($j = 0; $j < count($keys); $j++) {
									$vals .= ":" . $keys[$j];
									if ($j < count($keys) - 1) {
										$vals .= ",";
									}
								}

								$stmt = $db->prepare(" INSERT INTO twitchchannel  (
																			id,
																			" . implode(",", $keys) . "
																		) VALUES (
																			NULL,
																			" . $vals . "
																		);");

								$twitchUser = twitch_getUsersFromLogin($thisUsername);
								if (!$twitchUser || !$twitchUser['data'] || !$twitchUser['data'][0]) {
									$skips[] = $thisUsername;
									$countSkips++;
									continue;
								}

								$data['twitchId'] = $twitchUser['data'][0]['id'];
								$data['twitchUsername'] = $twitchUser['data'][0]['login'];
								$data['name'] = $twitchUser['data'][0]['display_name'];
								$data['twitchDescription'] = $twitchUser['data'][0]['description'];
								$data['twitchBroadcasterType'] = $twitchUser['data'][0]['broadcaster_type'];
								$data['twitchProfileImageUrl'] = $twitchUser['data'][0]['profile_image_url'];
								$data['twitchOfflineImageUrl'] = $twitchUser['data'][0]['offline_image_url'];
								$data['views'] = $twitchUser['data'][0]['view_count'];


 								for($j = 0; $j < count($keys); $j++) {
									$stmt->bindValue(":" . $keys[$j], $data[$keys[$j]], (is_numeric($data[$keys[$j]])? Database::VARTYPE_INTEGER : Database::VARTYPE_STRING));
								}

							 	// for($j = 0; $j < count($intoVals); $j++) {
							 	// 	$stmt->bindValue(":var" . $j, $intoVals[$j], Database::VARTYPE_STRING);
							 	// }
							 	$stmt->execute();
							 	$countImports++;

							}
							$result->message = "Imported " . $countImports . " items. Skipped " . $countSkips . " items (" . implode(",", $skips) . ").";

						}

					}


				}

			}


		}

		else if ($endpoint == "/person/list/")
		{
			$require_login = true;
			include_once("init.php");



			$people = $db->query("SELECT * FROM person WHERE removed = 0;");
			$num_people = count($people);
			//usort($people, "sortByName");
			usort($people, "sortById");

			for($i = 0; $i < $num_people; $i++) {
				$people[$i]['notes'] = utf8_encode($people[$i]['notes']);
			}

			$result = new stdClass();
			$result->success = true;
			$result->people = $people;

		}
		else if ($endpoint == "/publication/list/")
		{
			$require_login = true;
			include_once("init.php");

			$publications = $db->query("SELECT * FROM publication WHERE removed = 0;");
			//usort($publications, "sortByName");
			usort($publications, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->publications = $publications;
		}
		else if ($endpoint == "/person-publication/list/")
		{
			$require_login = true;
			include_once("init.php");

			$personPublications = $db->query("SELECT * FROM person_publication; ");
			usort($personPublications, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->personPublications = $personPublications;
		}
		else if ($endpoint == "/person-youtube-channel/list/")
		{
			$require_login = true;
			include_once("init.php");

			$personYoutubers = $db->query("SELECT * FROM person_youtuber; ");
			usort($personYoutubers, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->personYoutubeChannels = $personYoutubers;
		}
		else if ($endpoint == "/person-twitchchannel/list/")
		{
			$require_login = true;
			include_once("init.php");

			$personTwitchChannels = $db->query("SELECT * FROM person_twitchchannel; ");
			usort($personTwitchChannels, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->personTwitchChannels = $personTwitchChannels;
		}
		else if ($endpoint == "/youtuber/list/")
		{
			$require_login = true;
			include_once("init.php");

			$youtubeChannels = $db->query("SELECT * FROM youtuber WHERE removed = 0;");
			$num_youtubeChannels = count($youtubeChannels);
			//usort($youtubeChannels, "sortByName");
			usort($youtubeChannels, "sortById");

			for($i = 0; $i < $num_youtubeChannels; $i++) {
				$youtubeChannels[$i]['notes'] = utf8_encode($youtubeChannels[$i]['notes']);
				$youtubeChannels[$i]['description'] = utf8_encode($youtubeChannels[$i]['description']);
			}

			$result = new stdClass();
			$result->success = true;
			$result->youtubechannels = $youtubeChannels;
		}
		else if ($endpoint == "/twitchchannel/list/")
		{
			$require_login = true;
			include_once("init.php");

			$twitchchannels = $db->query("SELECT * FROM twitchchannel WHERE removed = 0;");
			$num_youtubeChannels = count($twitchchannels);
			//usort($youtubeChannels, "sortByName");
			usort($twitchchannels, "sortById");

			for($i = 0; $i < $num_youtubeChannels; $i++) {
				$twitchchannels[$i]['notes'] = utf8_encode($twitchchannels[$i]['notes']);
				$twitchchannels[$i]['description'] = utf8_encode($twitchchannels[$i]['description']);
			}

			$result = new stdClass();
			$result->success = true;
			$result->twitchchannels = $twitchchannels;
		}
		else if ($endpoint == "/email/list/")
		{
			$require_login = true;
			include_once("init.php");

			$emails = $db->query("SELECT * FROM email WHERE unmatchedrecipient = 0 AND removed = 0 ORDER BY utime DESC;");
			$num_emails = count($emails);
			for($i = 0; $i < $num_emails; $i++) {
				$emails[$i]['contents'] = utf8_encode($emails[$i]['contents']);
			}
			usort($emails, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->emails = $emails;
		}
		else if ($endpoint == '/email/remove/')
		{
			$require_login = true;
			include_once("init.php");

			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to remove emails.';
			}
			else {

				$id = $_GET['id'];
				if (!is_numeric($id) || $id <= 0 ) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'ID must be a number.';
				} else {

					$stmt = $db->prepare("SELECT * FROM email WHERE id = :id LIMIT 1 ;");
					$stmt->bindValue(":id", $id, Database::VARTYPE_STRING);
					$emails = $stmt->query();

					if (count($emails) != 1) {
						$result = new stdClass();
						$result->success = false;
						$result->message = 'Email does not exist.';
					} else {

						$stmt = $db->prepare("UPDATE email SET removed = 1 WHERE id = :id ;");
						$stmt->bindValue(":id", $id, Database::VARTYPE_STRING);
						$stmt->execute();

						$result = new stdClass();
						$result->success = true;

					}
				}

			}
		}


		else if ($endpoint == "/watchedgame/list/")
		{
			$require_login = true;
			include_once("init.php");

			$watchedgames = $db->query("SELECT watchedgame.*
										FROM watchedgame
										LEFT JOIN game_watchedgame on watchedgame.id = game_watchedgame.watchedgame_id
										WHERE game_watchedgame.game_id = {$user_currentGame} AND watchedgame.removed = 0 ORDER BY name ASC;");

			// TODO refactor this into two calls rather than two for each game.
			for($j = 0; $j < count($watchedgames); $j++) {

				$id = $watchedgames[$j]['id'];

				$publication_coverage = $db->query("SELECT * FROM publication_coverage WHERE watchedgame = {$id} AND removed = 0 ORDER BY utime DESC;");
				$num_publication_coverage = count($publication_coverage);
				for($i = 0; $i < $num_publication_coverage; $i++) {
					//$publication_coverage[$i]['title'] = utf8_encode($publication_coverage[$i]['title']);
					if ($publication_coverage[$i]['title'] == null) {
						$publication_coverage[$i]['title'] = "Untitled Article";
					}
					$publication_coverage[$i]['type'] = "publication";
				}

				$youtuber_coverage = $db->query("SELECT * FROM youtuber_coverage WHERE watchedgame = {$id} AND removed = 0 ORDER BY utime DESC;");
				$youtuber_coverage_coverage = count($youtuber_coverage);
				for($i = 0; $i < $youtuber_coverage_coverage; $i++) {
					$youtuber_coverage[$i]['type'] = "youtuber";
				}

				$coverage = array_merge($publication_coverage, $youtuber_coverage);
				usort($coverage, "sortByUtime");

				$watchedgames[$j]['coverage'] = $coverage;



			}

			$result = new stdClass();
			$result->success = true;
			$result->watchedgames = $watchedgames;

		}

		else if ($endpoint == "/coverage/")
		{
			$require_login = true;
			include_once("init.php");



			$publication_coverage = $db->query("SELECT * FROM publication_coverage WHERE game = {$user_currentGame} AND removed = 0 ORDER BY utime DESC;");
			$num_publication_coverage = count($publication_coverage);
			for($i = 0; $i < $num_publication_coverage; $i++) {
				//$publication_coverage[$i]['title'] = utf8_encode($publication_coverage[$i]['title']);
				if ($publication_coverage[$i]['title'] == null) {
					$publication_coverage[$i]['title'] = "Untitled Article";
				}
				$publication_coverage[$i]['type'] = "publication";
			}

			$youtuber_coverage = $db->query("SELECT * FROM youtuber_coverage WHERE game = {$user_currentGame} AND removed = 0 ORDER BY utime DESC;");
			$youtuber_coverage_coverage = count($youtuber_coverage);
			for($i = 0; $i < $youtuber_coverage_coverage; $i++) {
				$youtuber_coverage[$i]['type'] = "youtuber";
			}

			$twitchchannel_coverage = $db->query("SELECT * FROM twitchchannel_coverage WHERE game = {$user_currentGame} AND removed = 0 ORDER BY utime DESC;");
			$num_twitchchannel_coverage = count($twitchchannel_coverage);
			for($i = 0; $i < $num_twitchchannel_coverage; $i++) {
				$twitchchannel_coverage[$i]['type'] = "twitchchannel";
				$twitchchannel_coverage[$i]['thumbnail'] = str_replace("%{width}", "300", $twitchchannel_coverage[$i]['thumbnail']);
				$twitchchannel_coverage[$i]['thumbnail'] = str_replace("%{height}", "200", $twitchchannel_coverage[$i]['thumbnail']);
				// iconurl = iconurl.replace("\%{width}", "300");
				// iconurl = iconurl.replace("\%{height}", "300");
			}

			$coverage = array_merge($publication_coverage, $youtuber_coverage, $twitchchannel_coverage);

			usort($coverage, "sortByUtime");

			$result = new stdClass();
			$result->success = true;
			$result->coverage = $coverage;
		}
		else if ($endpoint == "/coverage/publication/add/")
		{
			$require_login = true;
			include_once("init.php");

			$stmt = $db->prepare(" INSERT INTO publication_coverage  (id, 	publication,  person,  game,  url,  title,  `utime`,  thanked, removed)
															  VALUES (NULL, 0, 		  	  0,        0,    :url, :title, :utime, :thanked, :removed); ");
			$stmt->bindValue(":url", "http://coverage.com/", Database::VARTYPE_STRING);
			$stmt->bindValue(":title", "A massive article about your game project.", Database::VARTYPE_STRING);
			$stmt->bindValue(":utime", time(), Database::VARTYPE_INTEGER);
			$stmt->bindValue(":thanked", 0, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
			$stmt->execute();

			$coverageId = $db->lastInsertRowID();

			$coverages = $db->query("SELECT * FROM publication_coverage WHERE id = {$coverageId};");
			$coverages[0]['type'] = "publication";
			$result = new stdClass();
			$result->success = true;
			$result->coverage = $coverages[0];
		}
		else if ($endpoint == "/coverage/publication/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'title', 'type' => 'textarea'),
				array('name' => 'url', 'type' => 'url'),
				array('name' => 'thanked', 'type' => 'boolean')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// validate person
				$person = $_GET['person'];
				if ($_GET['person'] != "" && !util_isInteger($person)) {
					$result = api_error("person was not an integer");
				} else {

					// validate publication
					$publication = $_GET['publication'];
					if ($_GET['publication'] != "" && !util_isInteger($publication)) {
						$result = api_error("publication was not an integer");
					} else {

						$title = "";
						$thanked = ($_GET['thanked'] == "true")?1:0;
						//die($_GET['thanked']);



						$stmt = $db->prepare(" UPDATE publication_coverage
												SET
													publication = :publication,
													person = :person,
													game = :game,
													url = :url,
													title = :title,
													utime = :utime,
													thanked = :thanked
												WHERE id = :id;");
						$stmt->bindValue(":publication", $publication, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":person", $person, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":url", $_GET['url'], Database::VARTYPE_STRING);
						$stmt->bindValue(":title", strip_tags(stripslashes($_GET['title'])), Database::VARTYPE_STRING);
						$stmt->bindValue(":utime", $_GET['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":thanked", $thanked, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
						//echo $_GET['title'];
						$coverages = $db->query("SELECT * FROM publication_coverage WHERE id = " . $_GET['id'] . ";");
						$coverages[0]['type'] = "publication";
						$result = new stdClass();
						$result->success = true;
						$result->coverage = $coverages[0];
						//$result->test = $_GET['title'];
					}
				}
			}
		}
		else if ($endpoint == "/coverage/publication/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$stmt = $db->prepare(" UPDATE publication_coverage SET removed = 1 WHERE id = :id");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}


		else if ($endpoint == "/coverage/youtuber/add/")
		{
			$require_login = true;
			include_once("init.php");

			$stmt = $db->prepare(" INSERT INTO youtuber_coverage  	 (id, 	youtuber,  person,  game,  url,  title,  thumbnail,  `utime`,  thanked, removed)
															  VALUES (NULL, 0, 		   0,       :game, :url, :title, :thumbnail, :utime,  :thanked, :removed); ");
			$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":url", "http://youtube.com/", Database::VARTYPE_STRING);
			$stmt->bindValue(":title", "An awesome video review of your game project.", Database::VARTYPE_STRING);
			$stmt->bindValue(":thumbnail", "http://www.youtube.com/yt/brand/media/image/YouTube-icon-full_color.png", Database::VARTYPE_STRING);
			$stmt->bindValue(":utime", time(), Database::VARTYPE_INTEGER);
			$stmt->bindValue(":thanked", 0, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
			$stmt->execute();

			$coverageId = $db->lastInsertRowID();

			$coverages = $db->query("SELECT * FROM youtuber_coverage WHERE id = {$coverageId};");
			$coverages[0]['type'] = "youtuber";
			$result = new stdClass();
			$result->success = true;
			$result->coverage = $coverages[0];
		}
		else if ($endpoint == "/coverage/youtuber/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'title', 'type' => 'textarea'),
				array('name' => 'url', 'type' => 'url'),
				array('name' => 'thanked', 'type' => 'boolean')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// validate person
				$person = $_GET['person'];
				if ($_GET['person'] != "" && !util_isInteger($person)) {
					$result = api_error("person was not an integer");
				} else {

					// validate publication
					$youtuber = $_GET['youtuber'];
					if ($_GET['youtuber'] != "" && !util_isInteger($youtuber)) {
						$result = api_error("youtuber was not an integer");
					} else {

						$title = "";
						$thanked = ($_GET['thanked'] == "true")?1:0;
						//die($_GET['thanked']);
						$video_id = substr($_GET['url'], strrpos($_GET['url'], "=") + 1);
						$thumbnail = ($video_id === FALSE)?"http://www.youtube.com/yt/brand/media/image/YouTube-icon-full_color.png":"https://i.ytimg.com/vi/{$video_id}/default.jpg";


						$stmt = $db->prepare(" UPDATE youtuber_coverage
												SET
													youtuber = :youtuber,
													person = :person,
													game = :game,
													url = :url,
													title = :title,
													thumbnail = :thumbnail,
													utime = :utime,
													thanked = :thanked
												WHERE id = :id;");
						$stmt->bindValue(":youtuber", $youtuber, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":person", $person, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":url", $_GET['url'], Database::VARTYPE_STRING);
						$stmt->bindValue(":title", strip_tags(stripslashes($_GET['title'])), Database::VARTYPE_STRING);
						$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
						$stmt->bindValue(":utime", $_GET['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":thanked", $thanked, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
						//echo $_GET['title'];
						$coverages = $db->query("SELECT * FROM youtuber_coverage WHERE id = " . $_GET['id'] . ";");
						$coverages[0]['type'] = "youtuber";
						$result = new stdClass();
						$result->success = true;
						$result->coverage = $coverages[0];
						//$result->test = $_GET['title'];
					}
				}
			}
		}
		else if ($endpoint == "/coverage/youtuber/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$stmt = $db->prepare(" UPDATE youtuber_coverage SET removed = 1 WHERE id = :id");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}

		else if ($endpoint == "/coverage/twitchchannel/add/")
		{
			$require_login = true;
			include_once("init.php");

			$stmt = $db->prepare(" INSERT INTO twitchchannel_coverage  	 (id, 	twitchchannel,  person,  game,  url,  title,  thumbnail,  `utime`,  thanked, removed)
															  VALUES      (NULL, 0, 		    0,       :game, :url, :title, :thumbnail, :utime,  :thanked, :removed); ");
			$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":url", "http://twitch.tv/", Database::VARTYPE_STRING);
			$stmt->bindValue(":title", "An awesome video stream of your game project.", Database::VARTYPE_STRING);
			$stmt->bindValue(":thumbnail", "", Database::VARTYPE_STRING);
			$stmt->bindValue(":utime", time(), Database::VARTYPE_INTEGER);
			$stmt->bindValue(":thanked", 0, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
			$stmt->execute();

			$coverageId = $db->lastInsertRowID();

			$coverages = $db->query("SELECT * FROM twitchchannel_coverage WHERE id = {$coverageId};");
			$coverages[0]['type'] = "twitchchannel";
			$result = new stdClass();
			$result->success = true;
			$result->coverage = $coverages[0];
		}
		else if ($endpoint == "/coverage/twitchchannel/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'title', 'type' => 'textarea'),
				array('name' => 'url', 'type' => 'url'),
				array('name' => 'thanked', 'type' => 'boolean')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// validate person
				$person = $_GET['person'];
				if ($_GET['person'] != "" && !util_isInteger($person)) {
					$result = api_error("person was not an integer");
				} else {

					// validate publication
					$twitchchannel = $_GET['twitchchannel'];
					if ($_GET['twitchchannel'] != "" && !util_isInteger($twitchchannel)) {
						$result = api_error("twitchchannel was not an integer");
					} else {

						$title = "";
						$thanked = ($_GET['thanked'] == "true")?1:0;
						//die($_GET['thanked']);
						$video_id = substr($_GET['url'], strrpos($_GET['url'], "=") + 1);
						$thumbnail = "";// ($video_id === FALSE)?"http://www.youtube.com/yt/brand/media/image/YouTube-icon-full_color.png":"https://i.ytimg.com/vi/{$video_id}/default.jpg";


						$stmt = $db->prepare(" UPDATE twitchchannel_coverage
												SET
													twitchchannel = :twitchchannel,
													person = :person,
													game = :game,
													url = :url,
													title = :title,
													thumbnail = :thumbnail,
													utime = :utime,
													thanked = :thanked
												WHERE id = :id;");
						$stmt->bindValue(":twitchchannel", $twitchchannel, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":person", $person, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":url", $_GET['url'], Database::VARTYPE_STRING);
						$stmt->bindValue(":title", strip_tags(stripslashes($_GET['title'])), Database::VARTYPE_STRING);
						$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
						$stmt->bindValue(":utime", $_GET['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":thanked", $thanked, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
						//echo $_GET['title'];
						$coverages = $db->query("SELECT * FROM twitchchannel_coverage WHERE id = " . $_GET['id'] . ";");
						$coverages[0]['type'] = "twitchchannel";
						$result = new stdClass();
						$result->success = true;
						$result->coverage = $coverages[0];
						//$result->test = $_GET['title'];
					}
				}
			}
		}
		else if ($endpoint == "/coverage/twitchchannel/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$stmt = $db->prepare(" UPDATE twitchchannel_coverage SET removed = 1 WHERE id = :id");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}

		else if ($endpoint == "/keys/list/")
		{
			$require_login = true;
			include_once("init.php");

			$dolist = true;
			if (!util_isValidPlatformForProjectKeys($_GET['platform'])) {
				$result = api_error("Invalid 'platform' value.");
				$dolist = false;
			//} else if ($_GET['assigned'] != "true" && $_GET['assigned'] != "false") {
			//	$result = api_error("Invalid 'assigned' value.");
			//	$dolist = false;
			} else if (!util_isInteger($_GET['game'])) {
				$result = api_error("Invalid 'game' value.");
				$dolist = false;
			}

			if ($dolist) { // make sure Game is valid.
				$game = db_singlegame($db, $_GET['game']);
				if (!$game) {
					$result = api_error("Invalid 'game' value.");
					$dolist = false;
				}
			}

			if ($dolist) {

				$assignedSQL = "";
				if ($_GET['assigned'] == "true") {
					$assignedSQL = " AND assignedToTypeId != 0 ";
				} else if ($_GET['assigned'] == "false") {
					$assignedSQL = " AND assignedToTypeId = 0 ";
				}

				$keys = $db->query("SELECT * FROM game_key
									WHERE
										game = '" . $user_currentGame . "' AND
										platform = '" . $_GET['platform'] . "' AND removed = 0
										{$assignedSQL}
									;");
				$result = new stdClass();
				$result->success = true;
				$result->keys = $keys;
				$result->count = count($keys);
			} else if (!$dolist && !$result){
				$result = api_error("Unknown error. Invalid value.");
			}
		}
		else if ($endpoint == "/keys/pop/")
		{
			$require_login = true;
			include_once("init.php");

			$dopop = true;
			if (!util_isValidPlatformForProjectKeys($_GET['platform'])) {
				$result = api_error("Invalid 'platform' value.");
				$dopop = false;
			//} else if ($_GET['assigned'] != "true" && $_GET['assigned'] != "false") {
			//	$result = api_error("Invalid 'assigned' value.");
			//	$dolist = false;
			} else if (!util_isInteger($_GET['game'])) {
				$result = api_error("Invalid 'game' value.");
				$dopop = false;
			}

			if ($dopop) { // make sure Game is valid.
				$game = db_singlegame($db, $_GET['game']);
				if (!$game) {
					$result = api_error("Invalid 'game' value.");
					$dopop = false;
				}
			}

			if ($dopop) { // make sure Game is valid.
				if (!isset($_GET['amount'])) {
					$result = api_error("Invalid 'amount' value.");
					$dopop = false;
				}
				else if (!util_isInteger($_GET['amount']) || $_GET['amount'] <= 0) {
					$result = api_error("Invalid 'amount' value.");
					$dopop = false;
				}
			}

			if ($dopop) {

				$stmt = $db->prepare("SELECT * FROM game_key
										WHERE game = :game
											AND platform = :platform
											AND assignedToTypeId = :assignedToTypeId
											AND removed = :removed
										LIMIT :amount
									;");
				$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":platform", $_GET['platform'], Database::VARTYPE_STRING);
				$stmt->bindValue(":assignedToTypeId", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":amount", $_GET['amount'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
				$keys = $stmt->query();

				$time = time();
				$keyIds = array();
				foreach ($keys as &$key) {
					$keyIds[] = $key['id'];
					$key['removed'] = 1;
					$key['removedByUser'] = $_SESSION['user'];
					$key['removedByUserTimestamp'] = $time;
				}

				if (count($keyIds) < $_GET['amount']) {
					$result = api_error("Amount (" . $_GET['amount']. ") was less than the numbebr of keys found so none could be removed.");
				} else {

					$stmt = $db->prepare("UPDATE game_key SET removed = :removed, removedByUser = :removedByUser, removedByUserTimestamp = :removedByUserTimestamp WHERE id in ( :id ) ;");
					$stmt->bindValue(":id", implode($keyIds, ','), Database::VARTYPE_STRING);
					$stmt->bindValue(":removed", 1, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":removedByUser", $_SESSION['user'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":removedByUserTimestamp", $time, Database::VARTYPE_INTEGER);
					$r = $stmt->execute();
					if (!$r) {
						$result = api_error("mysqli error" . $stmt->error);
						print_r($r);
					} else {

						$result = new stdClass();
						$result->success = true;
						$result->keys = $keys;
						$result->count = count($keys);
					}
				}
			} else if (!$dopop && !$result){
				$result = api_error("Unknown error. Invalid value.");
			}
		}
		else if ($endpoint == "/keys/add/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'keys', 'type' => 'alphanumerichyphensnewlines'),
				array('name' => 'game', 'type' => 'integer'),
				array('name' => 'platform', 'type' => 'platform'),
				array('name' => 'expiresOn', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$game = db_singlegame($db, $_GET['game']);
				if (!$game) {
					$result = api_error("Invalid 'game' value.");
					$dolist = false;
				}
				else {

					// Check format of keys.
					$anyInvalidKeys = false;
					$keysArray = explode("\n", $_GET['keys']);
					for($j = 0; $j < count($keysArray); $j++) {

						if (!util_isValidKeyFormat($_GET['platform'], $keysArray[$j], $result)) {
							$anyInvalidKeys = true;
							break;
						}
					}

					if (!$anyInvalidKeys) {
						// if we get here then the keys are all good!
						// TODO: should we check for duplicates..?
						for($j = 0; $j < count($keysArray); $j++) {
							$stmt = $db->prepare("INSERT INTO game_key (id, game, platform, keystring, assigned, assignedToType, assignedToTypeId, assignedByUser, assignedByUserTimestamp, createdOn, expiresOn, removed)
													  			VALUES (NULL, :game, :platform, :keystring, :assigned, :assignedToType, :assignedToTypeId, :assignedByUser, :assignedByUserTimestamp, :createdOn, :expiresOn, :removed ); ");
							$stmt->bindValue(":game", $user_currentGame, Database::VARTYPE_INTEGER);
							$stmt->bindValue(":platform", $_GET['platform'], Database::VARTYPE_STRING);
							$stmt->bindValue(":keystring", $keysArray[$j], Database::VARTYPE_STRING);
							$stmt->bindValue(":assigned", 0, Database::VARTYPE_INTEGER);
							$stmt->bindValue(":assignedToType", '', Database::VARTYPE_STRING);
							$stmt->bindValue(":assignedToTypeId", 0, Database::VARTYPE_INTEGER);
							$stmt->bindValue(":assignedByUser", 0, Database::VARTYPE_INTEGER);
							$stmt->bindValue(":assignedByUserTimestamp", 0, Database::VARTYPE_INTEGER);
							$stmt->bindValue(":createdOn", time(), Database::VARTYPE_INTEGER);
							$stmt->bindValue(":expiresOn", $_GET['expiresOn'], Database::VARTYPE_INTEGER);
							$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
							$stmt->execute();
						}

						$result = new stdClass();
						$result->success = true;
						$result->keys = $keysArray;
					} else {

					}
				}
			} else {
				$result = api_error("Unknown parameters passed e.g. platform value.");
			}

		}

		else if ($endpoint == "/person/add/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'firstname', 'type' => 'alphanumericspaces'),
				array('name' => 'surnames', 'type' => 'alphanumericspaces'),
				//array('name' => 'email', 'type' => 'email'),
				//array('name' => 'twitter', 'type' => 'alphanumeric'),
				//array('name' => 'notes', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare(" INSERT INTO person  (id,   firstname, surnames,  email, priorities,   twitter, twitter_followers,   notes, lastcontacted, lastcontactedby, removed)
													VALUES (NULL, :firstname, :surnames, :email, :priorities, :twitter, :twitter_followers, :notes, :lastcontacted, :lastcontactedby, :removed); ");
				$stmt->bindValue(":firstname", $_GET['firstname'], Database::VARTYPE_STRING);
				$stmt->bindValue(":surnames", $_GET['surnames'], Database::VARTYPE_STRING);
				$stmt->bindValue(":email", "", Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter", "", Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter_followers", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":priorities", db_defaultPrioritiesString($db), Database::VARTYPE_STRING);
				$stmt->bindValue(":notes", "", Database::VARTYPE_STRING);
				$stmt->bindValue(":lastcontacted", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":lastcontactedby", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
				$stmt->execute();

				$person_id = $db->lastInsertRowID();

				$result = new stdClass();
				$result->success = true;
				$result->person = db_singleperson($db, $person_id);
			}
		}
		else if ($endpoint == "/person/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'firstname', 'type' => 'alphanumericspaces'),
				//array('name' => 'surnames', 'type' => 'alphanumericspaces'),
				array('name' => 'email', 'type' => 'email'),
				array('name' => 'notes', 'type' => 'textarea'),
				array('name' => 'twitter', 'type' => 'alphanumericunderscores'),
				array('name' => 'outofdate', 'type' => 'boolean')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$surname = "";
				if ($_GET['surnames'] != "") {
					$surname = $_GET['surnames'];
				}

				$twitter_followers = twitter_countFollowers($_GET['twitter']);
				if ($twitter_followers == "") { $twitter_followers = 0; }
				$twitter_followers_sql = ($twitter_followers > 0)?" twitter_followers = :twitter_followers, ":"";
				$outofdate = ($_GET['outofdate'] == "true")?1:0;

				$stmt = $db->prepare(" UPDATE person SET firstname = :firstname, surnames = :surnames, email = :email, twitter = :twitter, " . $twitter_followers_sql . " notes = :notes, outofdate = :outofdate WHERE id = :id ");
				$stmt->bindValue(":firstname", $_GET['firstname'], Database::VARTYPE_STRING);
				$stmt->bindValue(":surnames", $surname, Database::VARTYPE_STRING);
				$stmt->bindValue(":email", strtolower(trim($_GET['email'])), Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter", $_GET['twitter'], Database::VARTYPE_STRING);
				if ($twitter_followers > 0) {
					$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_INTEGER);
				}
				$stmt->bindValue(":notes", strip_tags(stripslashes($_GET['notes'])), Database::VARTYPE_STRING);
				$stmt->bindValue(":outofdate", $outofdate, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->person = db_singleperson($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/person/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare("UPDATE person SET removed = 1 WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_STRING);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/person/add-publication/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'person', 'type' => 'integer'),
				array('name' => 'publication', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// make sure this user doesn't have this publication already.
				$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_publication WHERE person = :person AND publication = :publication");
				$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_STRING);
				$stmt->bindValue(":publication", $_GET['publication'], Database::VARTYPE_STRING);
				$row = $stmt->query();
				if ($row[0]['count'] > 0) {
					$result = api_error("This person already has this publication attached.");
				} else {

					$stmt = $db->prepare(" INSERT INTO person_publication (id, person, publication, email, lastcontacted, lastcontactedby) VALUES (NULL, :person, :publication, :email, :lastcontacted, :lastcontactedby); ");
					$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_STRING);
					$stmt->bindValue(":publication", $_GET['publication'], Database::VARTYPE_STRING);
					$stmt->bindValue(":email", "", Database::VARTYPE_STRING);
					$stmt->bindValue(":lastcontacted", 0, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", 0, Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();

					$personPublication_id = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->personPublication = db_singlepersonpublication($db, $personPublication_id);
				}
			}
		}
		else if ($endpoint == "/person/save-publication/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'personPublication', 'type' => 'integer'),
				array('name' => 'email', 'type' => 'email')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" UPDATE person_publication set email = :email WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['personPublication'], Database::VARTYPE_STRING);
				$stmt->bindValue(":email", $_GET['email'], Database::VARTYPE_STRING);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->personPublication = db_singlepersonpublication($db, $_GET['personPublication']);
			}
		}
		else if ($endpoint == "/person/remove-publication/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'personPublication', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" DELETE FROM person_publication WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['personPublication'], Database::VARTYPE_STRING);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/person/add-youtube-channel/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'person', 'type' => 'integer'),
				array('name' => 'youtubeChannel', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// make sure this user doesn't have this youtube channel already.
				$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_youtuber WHERE person = :person AND youtuber = :youtuber ;");
				$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":youtuber", $_GET['youtubeChannel'], Database::VARTYPE_INTEGER);
				$row = $stmt->query();
				if ($row[0]['count'] > 0) {
					$result = api_error("This person already has this Youtube Channel attached.");
				} else {

					$stmt = $db->prepare(" INSERT INTO person_youtuber (id, person, youtuber) VALUES (NULL, :person, :youtuber); ");
					$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":youtuber", $_GET['youtubeChannel'], Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();

					$personYoutuber_id = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->personYoutubeChannel = db_singlepersonyoutubechannel($db, $personYoutuber_id);
				}
			}
		}
		else if ($endpoint == "/person/remove-youtube-channel/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'personYoutubeChannel', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" DELETE FROM person_youtuber WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['personYoutubeChannel'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/person/add-twitchchannel/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'person', 'type' => 'integer'),
				array('name' => 'twitchchannel', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				// make sure this user doesn't have this youtube channel already.
				$stmt = $db->prepare("SELECT COUNT(*) as count FROM person_twitchchannel WHERE person = :person AND twitchchannel = :twitchchannel ;");
				$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":twitchchannel", $_GET['twitchchannel'], Database::VARTYPE_INTEGER);
				$row = $stmt->query();
				if ($row[0]['count'] > 0) {
					$result = api_error("This person already has this Twitch Channel attached.");
				} else {

					$stmt = $db->prepare(" INSERT INTO person_twitchchannel (id, person, twitchchannel) VALUES (NULL, :person, :twitchchannel); ");
					$stmt->bindValue(":person", $_GET['person'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":twitchchannel", $_GET['twitchchannel'], Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();

					$personTwitchChannel_id = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->personTwitchChannel = db_singlepersontwitchchannel($db, $personTwitchChannel_id);
				}
			}
		}
		else if ($endpoint == "/person/remove-twitchchannel/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'personTwitchChannel', 'type' => 'integer')
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error)
			{
				$stmt = $db->prepare(" DELETE FROM person_twitchchannel WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['personTwitchChannel'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}

		else if ($endpoint == "/person/set-assignment/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'user', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// TODO: validate that id passed is actually a person.
				// TODO: validate that assigned passed is actually a user id (or is 0 for n/a)

				$singlePerson = db_singleperson($db, $_GET['id']);
				$assigned = $_GET['user'];

				$stmt = $db->prepare(" UPDATE person SET assigned = :assigned WHERE id = :id ");
				$stmt->bindValue(":assigned", $assigned, Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->person = db_singleperson($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/person/set-priority/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'priority', 'type' => 'priority'),
				array('name' => 'game', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// TODO: validate that game passed is actually a game.
				// TODO: validate that id passed is actually a person.

				$singlePerson = db_singleperson($db, $_GET['id']);
				$games = explode(",", $singlePerson['priorities']);
				$foundGame = false;
				for($i = 0; $i < count($games); $i++) {
					$pieces = explode("=", $games[$i]);
					if ($pieces[0] == $_GET['game']) {
						$foundGame = true;
						$pieces[1] = $_GET['priority'];
						$games[$i] = implode("=", $pieces);
					}
				}
				if ($foundGame == false) {
					$games[] = $_GET['game'] . "=" . $_GET['priority'];
				}
				$priorities = implode(",", $games);

				$stmt = $db->prepare(" UPDATE person SET priorities = :priorities WHERE id = :id ");
				$stmt->bindValue(":priorities", $priorities, Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->person = db_singleperson($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/publication/add/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'name', 'type' => 'alphanumericspaces')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare(" INSERT INTO publication (id,   name,  url,  iconurl, rssfeedurl, twitter, twitter_followers,	notes, lastpostedon)
														VALUES (NULL, :name, :url, :iconurl, :rssfeedurl, :twitter, :twitter_followers, :notes, :lastpostedon); ");
				$stmt->bindValue(":name", $_GET['name'], Database::VARTYPE_STRING);
				$stmt->bindValue(":url", "http://example.com/", Database::VARTYPE_STRING);
				$stmt->bindValue(":iconurl", "images/favicon.png", Database::VARTYPE_STRING);
				$stmt->bindValue(":rssfeedurl", "http://example.com/rss/", Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter", "", Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter_followers", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":notes", "", Database::VARTYPE_STRING);
				$stmt->bindValue(":lastpostedon", 0, Database::VARTYPE_INTEGER);
				$stmt->execute();

				$publication_id = $db->lastInsertRowID();

				$result = new stdClass();
				$result->success = true;
				$result->publication = db_singlepublication($db, $publication_id);

			}
		}
		else if ($endpoint == "/publication/set-priority/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'priority', 'type' => 'priority'),
				array('name' => 'game', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// TODO: validate that game passed is actually a game.
				// TODO: validate that id passed is actually a publication.

				$singlePublication = db_singlepublication($db, $_GET['id']);
				$games = explode(",", $singlePublication['priorities']);
				$foundGame = false;
				for($i = 0; $i < count($games); $i++) {
					$pieces = explode("=", $games[$i]);
					if ($pieces[0] == $_GET['game']) {
						$foundGame = true;
						$pieces[1] = $_GET['priority'];
						$games[$i] = implode("=", $pieces);
					}
				}
				if ($foundGame == false) {
					$games[] = $_GET['game'] . "=" . $_GET['priority'];
				}
				$priorities = implode(",", $games);

				$stmt = $db->prepare(" UPDATE publication SET priorities = :priorities WHERE id = :id ");
				$stmt->bindValue(":priorities", $priorities, Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER
					);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->publication = db_singlepublication($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/publication/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'name', 'type' => 'alphanumericspaces'),
				array('name' => 'url', 'type' => 'url'),
				array('name' => 'rssfeedurl', 'type' => 'url'),
				array('name' => 'twitter', 'type' => 'alphanumericunderscores'),
				array('name' => 'notes', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$twitter_followers = twitter_countFollowers($_GET['twitter']);
				if ($twitter_followers == "") { $twitter_followers = 0; }
				$twitter_followers_sql = ($twitter_followers > 0)?" twitter_followers = :twitter_followers, ":"";

				$stmt = $db->prepare(" UPDATE publication SET name = :name, url = :url, email = :email, rssfeedurl = :rssfeedurl, twitter = :twitter, " . $twitter_followers_sql . " notes = :notes WHERE id = :id ");
				$stmt->bindValue(":name", $_GET['name'], Database::VARTYPE_STRING);
				$stmt->bindValue(":url", $_GET['url'], Database::VARTYPE_STRING);
				$stmt->bindValue(":email", strtolower(trim($_GET['email'])), Database::VARTYPE_STRING);
				$stmt->bindValue(":rssfeedurl", $_GET['rssfeedurl'], Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter", $_GET['twitter'], Database::VARTYPE_STRING);
				if ($twitter_followers > 0) {
					$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_INTEGER);
				}
				$stmt->bindValue(":notes", strip_tags(stripslashes($_GET['notes'])), Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->publication = db_singlepublication($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/publication/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare("UPDATE publication SET removed = 1 WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/youtuber/search-youtube/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'search', 'type' => 'textarea'),
				array('name' => 'order', 'type' => 'alphanumeric') // date/relevance/rating/title/viewCount
			);

			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$data = youtube_v3_search(urldecode($_GET['search']), $_GET['order']);
				$results = array();
				for($i = 0; $i < count($data['items']); $i++) {
					$one = array();
					$one['channel']['id'] = $data['items'][$i]['snippet']['channelId'];
					$one['channel']['title'] = $data['items'][$i]['snippet']['channelTitle'];
					$one['video']['id'] = $data['items'][$i]['id']['videoId'];
					$one['video']['thumbnail'] = $data['items'][$i]['snippet']['thumbnails']['default']['url'];
					$one['video']['title'] = $data['items'][$i]['snippet']['title'];
					$one['video']['description'] = $data['items'][$i]['snippet']['description'];
					$one['video']['timestamp'] = strtotime($data['items'][$i]['snippet']['publishedAt']);
					$results[] = $one;
				}

				$result = new stdClass();
				$result->success = true;
				$result->results = $results;
			}

		}
		else if ($endpoint == "/youtuber/add/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'channel', 'type' => 'alphanumerichyphens'),
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$twitter = "youtube";
				$twitter_followers = twitter_countFollowers($twitter);
				if ($twitter_followers == "") { $twitter_followers = 0; }
				$stmt = $db->prepare(" INSERT INTO youtuber (id, 	youtubeId, youtubeUploadsPlaylistId, name,   name_override, description, email, channel,  priorities, iconurl,   subscribers, views, notes, twitter,   twitter_followers, 	twitter_updatedon, lastpostedon, removed)
													VALUES  (NULL, '', '', 'Blank', 'Blank', 	 	 '', '',	:channel, '', 		  '', 		 0, 		  0, 	 '', 	:twitter, :twitter_followers, 0,	 0, 		  	0);	");
				$stmt->bindValue(":channel", $_GET['channel'], Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter", $twitter, Database::VARTYPE_STRING);
				$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_INTEGER);
				$stmt->execute();


				$youtuber_id = $db->lastInsertRowID();
				$result = new stdClass();
				$result->success = true;
				$result->followers = $twitter_followers;
				$result->youtubechannel = db_singleyoutubechannel($db, $youtuber_id);

			}
		}
		else if ($endpoint == "/youtuber/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'name', 'type' => 'textarea'),
				array('name' => 'channel', 'type' => 'textarea'),
				array('name' => 'email', 'type' => 'email'),
				array('name' => 'twitter', 'type' => 'alphanumericunderscores'),
				array('name' => 'notes', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$youtuber = youtube_v3_getInformation($_GET['channel']);
				if ($youtuber == 0) {
					$result = api_error("Youtube channel '" . $_GET['channel'] . "' not found.");
				} else {

					$twitter = $_GET['twitter'];
					$twitter_followers = 0;
					if (strlen($twitter) > 0) {
						$twitter_followers = twitter_countFollowers($_GET['twitter']);
					}
					if ($twitter_followers == "") { $twitter_followers = 0; }
					$twitter_followers_sql = ($twitter_followers > 0)?" twitter_followers = :twitter_followers, ":"";

					$stmt = $db->prepare(" UPDATE youtuber SET
												channel = :channel,
												name = :name,
												name_override = :name_override,
												description = :description,
												email = :email,
												iconurl = :iconurl,
												subscribers = :subscribers,
												views = :views,
												lastpostedon = :lastpostedon,
												twitter = :twitter,
												" . $twitter_followers_sql . "
												notes = :notes
											WHERE
												id = :id;
										");
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":channel", $_GET['channel'], Database::VARTYPE_STRING);
					$stmt->bindValue(":email", $_GET['email'], Database::VARTYPE_STRING);

					$ytname = $youtuber['name'];
					if (strlen(trim($ytname)) == 0) {
						$ytname = $_GET['name'];
					}
					$stmt->bindValue(":name", $ytname, Database::VARTYPE_STRING);
					$stmt->bindValue(":name_override", $_GET['name'], Database::VARTYPE_STRING);
					$stmt->bindValue(":description", $youtuber['description'], Database::VARTYPE_STRING);

					$ytIconUrl = $youtuber['iconurl'];
					if (strlen(trim($ytIconUrl)) == 0) {
						$ytIconUrl = "images/favicon.png";
					}

					$stmt->bindValue(":iconurl", $ytIconUrl, Database::VARTYPE_STRING);
					$stmt->bindValue(":subscribers", "" . $youtuber['subscribers'], Database::VARTYPE_STRING);
					$stmt->bindValue(":views", "" . $youtuber['views'], Database::VARTYPE_STRING);
					$stmt->bindValue(":lastpostedon", $youtuber['lastpostedon'], Database::VARTYPE_INTEGER);

					$stmt->bindValue(":twitter", $twitter, Database::VARTYPE_STRING);

					if ($twitter_followers > 0) {
						$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_INTEGER);
					}


					$stmt->bindValue(":notes", strip_tags(stripslashes($_GET['notes'])), Database::VARTYPE_STRING);

					$rs = $stmt->execute();

					$result = new stdClass();
					$result->success = true;
					$result->youtubechannel = db_singleyoutubechannel($db, $_GET['id']);
				}
			}
		}
		else if ($endpoint == "/youtuber/set-priority/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'priority', 'type' => 'priority'),
				array('name' => 'game', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// TODO: validate that game passed is actually a game.
				// TODO: validate that id passed is actually a youtuber.

				$singleYoutuber = db_singleyoutubechannel($db, $_GET['id']);
				$games = explode(",", $singleYoutuber['priorities']);
				$foundGame = false;
				for($i = 0; $i < count($games); $i++) {
					$pieces = explode("=", $games[$i]);
					if ($pieces[0] == $_GET['game']) {
						$foundGame = true;
						$pieces[1] = $_GET['priority'];
						$games[$i] = implode("=", $pieces);
					}
				}
				if ($foundGame == false) {
					$games[] = $_GET['game'] . "=" . $_GET['priority'];
				}
				$priorities = implode(",", $games);

				$stmt = $db->prepare(" UPDATE youtuber SET priorities = :priorities WHERE id = :id ");
				$stmt->bindValue(":priorities", $priorities, Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->youtubechannel = db_singleyoutubechannel($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/youtuber/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare("UPDATE youtuber SET removed = 1 WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/twitchchannel/add/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				//array('name' => 'channel', 'type' => 'textarea'),
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$twitchDefault = 'twitch';
				$twitch = twitch_getUsersFromLogin($twitchDefault);
				if (!$twitch) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Could not get Twitch channel defaults from Twitch API.";
				} else {
					$data = $twitch['data'][0];

					$subs = twitch_countSubscribers($data['id']);


					$twitter = "twitch";
					$twitter_followers = twitter_countFollowers($twitter);
					if ($twitter_followers == "") { $twitter_followers = 0; }

					$stmt = $db->prepare("INSERT INTO twitchchannel (
											`id`,
											`twitchId`, `twitchDescription`, `twitchBroadcasterType`, `twitchProfileImageUrl`, `twitchOfflineImageUrl`, `twitchUsername`,
											`name`,
											`email`,
											priorities,
											subscribers,
											`views`,
											twitter,
											twitter_followers,
											twitter_updatedon,
											notes,
											lang,
											lastpostedon,
											lastpostedon_updatedon,
											removed,
											lastscrapedon
										)
										VALUES (
											NULL,
											:twitchId, :twitchDescription, :twitchBroadcasterType, :twitchProfileImage, :twitchOfflineImage, :twitchUsername,
											:name,  :email, :priorities, :subs, 			 :views,  :twitter, 		:twitter_followers, :twitter_updatedon,      		'',  	'', 	0,  		0, 						0, 			0
										); ");
					$stmt->bindValue(":twitchId", $data['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":twitchDescription", $data['description'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchBroadcasterType", $data['broadcaster_type'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchProfileImage", $data['profile_image_url'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchOfflineImage", $data['offline_image_url'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchUsername", $data['login'], Database::VARTYPE_STRING);
					$stmt->bindValue(":name", $data['display_name'], Database::VARTYPE_STRING);
					$stmt->bindValue(":email", '', Database::VARTYPE_STRING);
					$stmt->bindValue(":priorities", db_defaultPrioritiesString($db), Database::VARTYPE_STRING);
					$stmt->bindValue(":views", $data['view_count'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":subs", $subs, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":twitter", $twitter, Database::VARTYPE_STRING);
					$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_STRING);
					$stmt->bindValue(":twitter_updatedon", time(), Database::VARTYPE_STRING);
					$stmt->execute();

					$twitchchannel_id = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->twitchchannel = db_singletwitchchannel($db, $twitchchannel_id);
				}

			}
		}
		else if ($endpoint == "/twitchchannel/save/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'channel', 'type' => 'textarea'),
				array('name' => 'email', 'type' => 'email'),
				array('name' => 'twitter', 'type' => 'alphanumericunderscores'),
				array('name' => 'notes', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				$twitch = twitch_getUsersFromLogin($_GET['channel']);
				if (!$twitch) {
					$result = api_error("Twitch channel '" . $_GET['channel'] . "' not found.");
				} else {

					//print_r($twitch);
					$data = $twitch['data'][0];

					$subs = twitch_countSubscribers($data['id']);

					$twitter = $_GET['twitter'];
					$twitter_followers = 0;
					if (strlen($twitter) > 0) {
						$twitter_followers = twitter_countFollowers($_GET['twitter']);
					}
					if ($twitter_followers == "") { $twitter_followers = 0; }
					$twitter_followers_sql = ($twitter_followers > 0)?" twitter_followers = :twitter_followers, ":"";

					$stmt = $db->prepare(" UPDATE twitchchannel SET
												twitchId = :twitchId,
												twitchDescription = :twitchDescription,
												twitchBroadcasterType = :twitchBroadcasterType,
												twitchProfileImageUrl = :twitchProfileImage,
												twitchOfflineImageUrl = :twitchOfflineImage,
												twitchUsername = :twitchUsername,
												name = :name,
												email = :email,
												subscribers = :subscribers,
												views = :views,
												lastpostedon = :lastpostedon,
												twitter = :twitter,
												" . $twitter_followers_sql . "
												notes = :notes
											WHERE
												id = :id;
										");
					$stmt->bindValue(":twitchId", intval($data['id']), Database::VARTYPE_INTEGER);
					$stmt->bindValue(":twitchDescription", $data['description'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchBroadcasterType", $data['broadcaster_type'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchProfileImage", $data['profile_image_url'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchOfflineImage", $data['offline_image_url'], Database::VARTYPE_STRING);
					$stmt->bindValue(":twitchUsername", $data['login'], Database::VARTYPE_STRING);

					$stmt->bindValue(":name", $data['display_name'], Database::VARTYPE_STRING);
					$stmt->bindValue(":email", $_GET['email'], Database::VARTYPE_STRING);
					$stmt->bindValue(":subscribers", "" . $subs, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":views", "" . $data['view_count'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastpostedon", 0, Database::VARTYPE_INTEGER);

					$stmt->bindValue(":twitter", $twitter, Database::VARTYPE_STRING);
					if ($twitter_followers > 0) {
						$stmt->bindValue(":twitter_followers", $twitter_followers, Database::VARTYPE_INTEGER);
					}
					$stmt->bindValue(":notes", strip_tags(stripslashes($_GET['notes'])), Database::VARTYPE_STRING);
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();


					$result = new stdClass();
					$result->success = true;
					$result->twitchchannel = db_singletwitchchannel($db, $_GET['id']);
				}
			}
		}
		else if ($endpoint == "/twitchchannel/set-priority/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'priority', 'type' => 'priority'),
				array('name' => 'game', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {

				// TODO: validate that game passed is actually a game.
				// TODO: validate that id passed is actually a youtuber.

				$singleTwitchChannel = db_singletwitchchannel($db, $_GET['id']);
				$games = explode(",", $singleTwitchChannel['priorities']);
				$foundGame = false;
				for($i = 0; $i < count($games); $i++) {
					$pieces = explode("=", $games[$i]);
					if ($pieces[0] == $_GET['game']) {
						$foundGame = true;
						$pieces[1] = $_GET['priority'];
						$games[$i] = implode("=", $pieces);
					}
				}
				if ($foundGame == false) {
					$games[] = $_GET['game'] . "=" . $_GET['priority'];
				}
				$priorities = implode(",", $games);

				$stmt = $db->prepare(" UPDATE twitchchannel SET priorities = :priorities WHERE id = :id ");
				$stmt->bindValue(":priorities", $priorities, Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
				$result->twitchchannel = db_singletwitchchannel($db, $_GET['id']);
			}
		}
		else if ($endpoint == "/twitchchannel/remove/")
		{
			$require_login = true;
			include_once("init.php");

			$required_fields = array(
				array('name' => 'id', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				$stmt = $db->prepare("UPDATE twitchchannel SET removed = 1 WHERE id = :id;");
				$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$result = new stdClass();
				$result->success = true;
			}
		}
		else if ($endpoint == "/project/add/")
		{
			$require_login = true;
			include_once("init.php");

			// user must be an admin to do this.
			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to add projects.';
			}
			else {
				$data = $_GET;

				if (!isset($data['name']) || !isset($data['iconurl'])) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'Name and icon must be set.';
				} else {

					$stmt = $db->prepare(" INSERT INTO game (id, name, keywords, iconurl)
													VALUES (NULL, :name, :iconurl) ");
					$stmt->bindValue(":name", 		$data['name'], 		Database::VARTYPE_STRING);
					$stmt->bindValue(":keywords", 	"", 		Database::VARTYPE_STRING);
					$stmt->bindValue(":iconurl", 	$data['iconurl'], 	Database::VARTYPE_STRING);
					$rs = $stmt->execute();

					$projectId = $db->lastInsertRowID();

					$result = new stdClass();
					$result->success = true;
					$result->project = db_singlegame($db, $projectId);
				}
			}
		}
		else if ($endpoint == "/admin/user/add/")
		{
			$require_login = true;
			include_once("init.php");

			// user must be an admin to do this.
			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to add users.';
			}
			else {
				$data = $_GET;

				$stmt = $db->prepare(" INSERT INTO user (id, forename, surname, email, password, color, admin, currentGame, coverageNotifications)
												VALUES (NULL, :forename, :surname, :email, :password, :color, :admin, :currentGame, :coverageNotifications ) ");
				$stmt->bindValue(":forename", 'Firstname', Database::VARTYPE_STRING);
				$stmt->bindValue(":surname", 'Surname', Database::VARTYPE_STRING);
				$stmt->bindValue(":email", 'test@gmail.com', Database::VARTYPE_STRING);
				$stmt->bindValue(":password", md5('password'), Database::VARTYPE_STRING);
				$stmt->bindValue(":color", 'black', Database::VARTYPE_STRING);
				$stmt->bindValue(":admin", 0, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":currentGame", 1, Database::VARTYPE_INTEGER);
				$stmt->bindValue(":coverageNotifications", 0, Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();

				$userId = $db->lastInsertRowID();

				$result = new stdClass();
				$result->success = true;
				$result->user = db_singleuser($db, $userId);
			}
		}
		else if ($endpoint == "/admin/user/save/")
		{
			$require_login = true;
			include_once("init.php");

			// user must be an admin to do this.
			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to edit users.';
			}
			else {
				$data = $_GET;

				$user = db_singleuser($db, $data['id']);
				if (!$user) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'This user does not exist.';
				} else {

					$required_fields = array(
						array('name' => 'id', 'type' => 'integer'),
						array('name' => 'forename', 'type' => 'string'),
						array('name' => 'surname', 'type' => 'string'),
						array('name' => 'email', 'type' => 'email'),
						array('name' => 'color', 'type' => 'string'),
						array('name' => 'admin', 'type' => 'bool')
					);
					$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
					if (!$error) {
						$stmt = $db->prepare(" UPDATE user SET
													forename = :forename,
													surname = :surname,
													email = :email,
													color = :color,
													admin = :admin
												WHERE id = :id ; ");
						$stmt->bindValue(":id", $data['id'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":forename", $data['forename'], Database::VARTYPE_STRING);
						$stmt->bindValue(":surname", $data['surname'], Database::VARTYPE_STRING);
						$stmt->bindValue(":email", $data['email'], Database::VARTYPE_STRING);
						$stmt->bindValue(":color", $data['color'], Database::VARTYPE_STRING);
						$stmt->bindValue(":admin", $data['admin'], Database::VARTYPE_INTEGER);
						$rs = $stmt->execute();

						$result = new stdClass();
						$result->success = true;
						$result->user = db_singleuser($db, $data['id']);
					}
				}
			}
		}
		else if ($endpoint == "/admin/user/remove/")
		{
			$require_login = true;
			include_once("init.php");

			// user must be an admin to do this.
			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to add users.';
			}
			else {
				$data = $_GET;
				$user = db_singleuser($db, $data['id']);
				if (!$user) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'This user does not exist.';
				} else {
					$stmt = $db->prepare(" UPDATE user SET removed = 1 WHERE id = :id ; ");
					$stmt->bindValue(":id", $data['id'], Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();

					$result = new stdClass();
					$result->success = true;
				}

			}
		}
		else if ($endpoint == "/admin/user/change-password/")
		{
			$require_login = true;
			include_once("init.php");

			// user must be an admin to do this.
			$user = db_singleuser($db, $_SESSION['user']);
			if (!$user['admin']) {
				$result = new stdClass();
				$result->success = false;
				$result->message = 'You must be an admin to add users.';
			}
			else {
				$data = $_GET;
				$user = db_singleuser($db, $data['id']);
				if (!$user) {
					$result = new stdClass();
					$result->success = false;
					$result->message = 'This user does not exist.';
				} else {

					if (!isset($data['password1']) || !isset($data['password2'])) {
						$result = new stdClass();
						$result->success = false;
						$result->message = 'One of the password fields was not set.';
					} else {

						if ($data['password1'] != $data['password2']) {
							$result = new stdClass();
							$result->success = false;
							$result->message = 'The passwords entered did not match.';
						} else {

							$stmt = $db->prepare(" UPDATE user SET password = :password WHERE id = :id ; ");
							$stmt->bindValue(":id", $data['id'], Database::VARTYPE_INTEGER);
							$stmt->bindValue(":password", md5($data['password1']), Database::VARTYPE_STRING);
							$rs = $stmt->execute();

							$result = new stdClass();
							$result->success = true;
						}
					}


				}

			}
		}
		else if ($endpoint == "/admin/sql-query/")
		{
			$require_login = true;
			include_once("init.php");

			//error_reporting(0);
			$required_fields = array(
				array('name' => 'query', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				/*$query = $_GET['query'];
				if (get_magic_quotes_gpc()) { $query = stripslashes($query); }
				$query = SQLite3::escapeString($query);

				$stmt = $db->prepare($query);
				$rs = $stmt->execute();
				if ($rs instanceof Sqlite3Result || $rs === TRUE) {
					$result = new stdClass();
					$result->success = true;
					$result->query = $query;
					$result->results = array();
					while ($arr = $rs->fetchArray(SQLITE3_ASSOC)) {
						$result->results[] = $arr;
					}
					$rs->finalize();
					//$stmt->close();

				} else {
					$result = api_error("Query was not successful: " .  $query);
				}*/
				$result = api_error("This API call is disabled. ");
			}
		}
		else if ($endpoint == "/user/change-imap-settings/")
		{
			$require_login = true;
			include_once("init.php");

			//error_reporting(0);
			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'smtpServer', 'type' => 'textarea'),
				array('name' => 'imapServer', 'type' => 'textarea'),
				array('name' => 'imapPassword', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				if ($user_id != $_GET['id']) {
					$result = api_error("You can only change your own IMAP settings.");
				} else {

					$smtpServer = $_GET['smtpServer'];
					$imapServer = $_GET['imapServer'];
					$imapPassword = $_GET['imapPassword'];

					$imapPasswordSalt = util_getSalt($imapPassword);
					$imapPasswordIV = util_getIV(true);
					$imapPasswordEncrypted = util_encrypt($imapPassword, $imapPasswordSalt);

					$stmt = $db->prepare("UPDATE user
											SET
												emailSMTPServer = :smtpServer,
												emailIMAPServer = :imapServer,
												emailIMAPPassword = :imapPassword,
												emailIMAPPasswordSalt = :imapPasswordSalt,
												emailIMAPPasswordIV = :imapPasswordIV
											WHERE id = :id; ");
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":smtpServer", $imapServer, Database::VARTYPE_STRING);
					$stmt->bindValue(":imapServer", $imapServer, Database::VARTYPE_STRING);
					$stmt->bindValue(":imapPassword", $imapPasswordEncrypted, Database::VARTYPE_STRING);
					$stmt->bindValue(":imapPasswordSalt", $imapPasswordSalt, Database::VARTYPE_STRING);
					$stmt->bindValue(":imapPasswordIV", $imapPasswordIV, Database::VARTYPE_STRING);
					$rs = $stmt->execute();

					$result = new stdClass();
					$result->success = true;
				}
			}

		}
		else if ($endpoint == "/user/change-password/")
		{
			$require_login = true;
			include_once("init.php");

			//error_reporting(0);
			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'currentPassword', 'type' => 'textarea'),
				array('name' => 'newPassword', 'type' => 'textarea')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				if ($user_id != $_GET['id']) {
					$result = api_error("You can only change your own password.");
				} else {
					$stmt = $db->prepare("SELECT * FROM user WHERE id = :id AND password = :currentPassword; ");
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":currentPassword", md5($_GET['currentPassword']), Database::VARTYPE_STRING);
					$users = $stmt->query();

					if (count($users) == 0) {
						$result = api_error("Your current password was wrong.");
					} else if (count($users) > 1 || count($users) < 0) {
						$result = api_error("Something went terribly wrong. Please inform an administrator.");
					} else {
						$newPassword = $_GET['newPassword'];
						//if ($newPassword == "password") {
						//	$result = api_error("Your password cannot be 'password'.");
						//} else
						if (strlen($newPassword) < 8) {
							$result = api_error("Your password must be 8 characters long.");
						} else {
							$stmt = $db->prepare("UPDATE user
													SET
														password = :newPassword
													WHERE
														id = :id AND
														password = :currentPassword
												;");
							$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
							$stmt->bindValue(":currentPassword", md5($_GET['currentPassword']), Database::VARTYPE_STRING);
							$stmt->bindValue(":newPassword", md5($newPassword), Database::VARTYPE_STRING);
							$rs = $stmt->execute();

							$result = new stdClass();
							$result->success = true;
						}
					}
				}


			}

		}
		else if ($endpoint == "/user/change-project/")
		{
			$require_login = true;
			include_once("init.php");

			//error_reporting(0);
			$required_fields = array(
				array('name' => 'id', 'type' => 'integer'),
				array('name' => 'newProject', 'type' => 'integer')
			);
			$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			if (!$error) {
				if ($user_id != $_GET['id']) {
					$result = api_error("You can only change your own project.");
				} else {

					$newProject = $_GET['newProject'];

					$stmt = $db->prepare("UPDATE user SET currentGame = :newProject WHERE id = :id ;");
					$stmt->bindValue(":id", $_GET['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":newProject", $newProject, Database::VARTYPE_INTEGER);
					$rs = $stmt->execute();
					if (!$rs) {
						$result = new stdClass();
						$result->success = false;
					} else {
						$result = new stdClass();
						$result->success = true;
					}
				}


			}

		}
		// Chat functionality...
		else if ($endpoint == "/chat/online-users/") {
			$require_login = true;
			include_once("init.php");

			if (!isset($_SESSION['user']) || !$_SESSION['user']) {
				$result = api_error("You are not logged in.");
			} else {
				// Update current user time.
				//$stmt = $db->prepare("UPDATE user SET lastactivity = :lastactivity WHERE id = :id;");
				//$stmt->bindValue(":lastactivity", time(), Database::VARTYPE_INTEGER);
				//$stmt->bindValue(":id", $_SESSION['user'], Database::VARTYPE_INTEGER);
				//$stmt->execute();
				user_updateActivity($_SESSION['user']);


				// Fetch other logged-in users.
				$stmt = $db->prepare("SELECT id, forename, surname, email, color, lastactivity FROM user WHERE lastactivity >= :lastactivity; ");
				$stmt->bindValue(":lastactivity", time(), Database::VARTYPE_INTEGER);
				$rs = $stmt->query();
				$results = array();
				foreach ($rs as $row) {
					$results[] = $row['id'];
				}

				$result = new stdClass();
				$result->success = true;
				$result->data = array("users" => $results);

			}

			//$rs = $sql->query("UPDATE user SET LastActivity = NOW() WHERE Id = '" . $_SESSION['User']['Id'] . "'");

		}
		else if ($endpoint == "/chat/lines/") {

			$required_fields = array(
				array('name' => 'time', 'type' => 'integer'),
				array('name' => 'size', 'type' => 'integer')
			);
			//$error = api_checkRequiredGETFieldsWithTypes($required_fields, $result);
			//if (!$error) {
				set_time_limit(0);
				session_start();
				$chat_file = $_SERVER['DOCUMENT_ROOT'] . "/data/chat.txt";

				$latest_message_time = $_GET['time'];
				$current_filesize = $_GET['size'];

				$filetime = filemtime($chat_file);

				// TODO: get long-polling working properly.
				for ($i = 0; $i < 1; $i++) {
					$filesize = filesize($chat_file);
					$filetime = filemtime($chat_file);
					//if ($filesize > $current_filesize) {
					if ($latest_message_time < $filetime) {
						// new messages!
						$f = fopen($chat_file, 'r');
						//stream_set_blocking($f, 0);
						$alllines = stream_get_contents($f);
						fclose($f);
						$lines = explode("\n", $alllines);
						$new_lines = array();
						for ($j = 0; $j < count($lines); $j++) {
							$line = $lines[$j];
							$o = json_decode($line);
							if ($o != null && $o->time >= $latest_message_time) {
								$new_lines[] = $o;
								$latest_message_time = $o->time;
							}
						}
						if (count($lines) > 1 && $current_filesize != 0) {
							array_shift($new_lines);
						}

						//if (count($new_lines) >= 1) {
						$result = new stdClass();
						$result->success = true;
						$result->data = array("lines" => $new_lines, "meta" => array("time" => $filetime, "size" => $filesize, "iteration" => $i));
						echo json_encode($result);
						die();
						//}
					}
					//usleep(1000000);
					@clearstatcache(true, $chat_file);
					sleep(1);

				}

				$new_lines = array();
				$result = new stdClass();
				$result->success = true;
				$result->data = array("lines" => $new_lines, "meta" => array("time" => $filetime, "size" => $filesize, "iteration" => 0));
				echo json_encode($result);
				die();
			//}

		}
		else if ($endpoint == "/chat/send/")
		{
			if (empty($_POST)) {
				$result = api_error("No chat data sent to the API.");
			} else {

				// check POST data.

				session_start();
				$chat_file = $_SERVER['DOCUMENT_ROOT'] . "/data/chat.txt";

				$message = $_POST['message'];
				$msg = str_replace ("\n"," ", $message);

				// if the user writes something the new message is appended to the msg.txt file
				// strip avoid buggy html code and slashes
				$msg = str_replace ("\n"," ", $msg);
				$msg = str_replace ("<", " ", $msg);
				$msg = str_replace (">", " ", $msg);
				$msg = stripslashes ($msg);

				if ($msg != ""){
					//list($usec, $sec) = explode(" ", microtime(false));
					//$sec .= substr($usec, 2, 3);

					$user = $_SESSION['user'];
					$array = array("time" => time(), "user" => $user, "message" => $msg);
					$fp = fopen($chat_file, "a");
					//stream_set_blocking($fp, 0);
					$fw = fwrite($fp, "\n" . json_encode($array));
					fclose($fp);
					@clearstatcache(true, $chat_file);
				}
				$result = new stdClass();
				$result->success = true;
				$result->message = $msg;
				$result->newsize = filesize($chat_file);


			}

		}
	}


}


api_result($result);

//$db->close();
//die();


?>
