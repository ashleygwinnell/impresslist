<?php



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
				if ($temp != 'steam') {
					$result = api_error($fields[$i]['name'] . " is not a valid platform. Steam only right now. -- " . $temp);
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

$result = null;
if (!isset($_GET['endpoint'])) {
	$result = api_error("endpoint was not set.");
} else {

	$endpoints = array(
		
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
		"/youtuber/add/",
		"/youtuber/save/",
		"/youtuber/set-priority/",
		"/youtuber/remove/",

		// Admin
		"/admin/sql-query/",
		"/admin/user/add/",
		"/backup/",
		"/backup-sql/",

		// User settings
		"/user/change-imap-settings/",
		"/user/change-password/",

		"/job/list/",
		"/job/save-all/",

		"/person-publication/list/",
		"/person-youtube-channel/list/",
		"/email/list/",

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

		// Coverage management
		"/coverage/",
		"/coverage/publication/add/",
		"/coverage/publication/save/",
		"/coverage/publication/remove/",
		"/coverage/youtuber/add/",
		"/coverage/youtuber/save/",
		"/coverage/youtuber/remove/",

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

		// Chat
		"/chat/online-users/",
		"/chat/lines/",
		"/chat/send/",

		// Test...
		"/test/test/"
	);
	$endpoint = $_GET['endpoint'];
	if (!in_array($endpoint, $endpoints)) {
		$result = api_error("API endpoint " . $endpoint . " does not exist.");
	} else { 

		if ($endpoint == "/test/test/")  
		{
			$require_login = false;
			include_once('init.php');

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
		else if ($endpoint == "/social/timeline/") 
		{
			$require_login = true;
			include_once('init.php');

			$includeSent = ($_GET['sent'] == "true")?1:0;
			$sentSQL = "";
			if (isset($_GET['sent'])) {
				$sentSQL = " AND sent = {$includeSent} ";
			}

			$results = $db->query("SELECT * FROM socialqueue WHERE removed = 0 " . $sentSQL . " ORDER BY `timestamp` ASC;");
		
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

			$uploads = scandir("images/uploads/");

			// sort by upload date
			function sortbyuploaddate($a, $b) {
				return filemtime("images/uploads/".$a) > filemtime("images/uploads/".$b);
			}
			usort($uploads, "sortbyuploaddate");

			foreach ($uploads as $upload) { 

				$ext = substr($upload, strlen($upload)-3, 3);
				if ($ext == "png" || $ext == "jpg" || $ext == "gif") {
					$result->uploads[] = array(
						"name" => $upload,
						"type" => "image/{$ext}",
						"size" => filesize("images/uploads/".$upload)
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
						if (file_exists("images/uploads/" . $_FILES["file"]["name"])) {
							$result = new stdClass();
							$result->success = false;
							$result->message = $_FILES["file"]["name"] . " already exists.";
						}
						else
						{
							$sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable
							$targetPath = "images/uploads/".$_FILES['file']['name']; // Target path where file is to be stored
							move_uploaded_file($sourcePath, $targetPath) ; // Moving Uploaded file

							$result = new stdClass();
							$result->success = true;
							$result->upload = array(
								"name" => $_FILES["file"]["name"],
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

				$exists = file_exists("images/uploads/" . $name);
				if (!$exists) {
					$result = new stdClass();
					$result->success = false;
					$result->message = "Social Upload {$name} does not exist. ";
				} else {
					$r = unlink("images/uploads/" . $name);
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
				$connection = new TwitterOAuth($twitter_consumerKey, $twitter_consumerSecret, $_GET['request_token'], $_GET['request_token_secret']);
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

					if (strpos($markdown, "{{steam_key}}") !== false) {
						$recipientsArray = json_decode($mailout['recipients'], true);
						$countRecipients = count($recipientsArray);
						// TODO: game_id should probably be part of the mailout data?! 
						$keysArray = $db->query("SELECT * 
											FROM game_key 
											WHERE 
												game = '" . $user_currentGame . "' AND 
												platform = 'steam' AND
												assigned = 0;"); 
						$countKeys = count($keysArray);

						if ($countKeys < $countRecipients) {
							$doSend = false;
							$numNewKeysNeeded = $countRecipients - $countKeys;
							$result = api_error("There are not enough Steam keys in the system to allocate to this mailout. {$numNewKeysNeeded} more needed.");
						}
					}
					if (strpos($markdown, "{{steam_keys}}") !== false) 
					{
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
												platform = 'steam' AND
												assigned = 0;"); 
						$countKeys = count($keysArray);
						if ($countKeys < $newKeysNeeded) {
							$numNewKeysNeeded = $newKeysNeeded - $countKeys;
							$doSend = false;
							$result = api_error("There are not enough Steam keys in the system to allocate to this mailout. {$numNewKeysNeeded} more needed.");
						}
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

						print_r($csv);
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
						$result->data = $list;
						$result->order = $importOrder;

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
		else if ($endpoint == "/email/list/") 
		{
			$require_login = true;
			include_once("init.php");

			$emails = $db->query("SELECT * FROM email WHERE unmatchedrecipient = 0 ORDER BY utime DESC;");
			$num_emails = count($emails);
			for($i = 0; $i < $num_emails; $i++) { 
				$emails[$i]['contents'] = utf8_encode($emails[$i]['contents']);
			}
			usort($emails, "sortById");

			$result = new stdClass();
			$result->success = true;
			$result->emails = $emails;
		}



		else if ($endpoint == "/coverage/") 
		{
			$require_login = true;
			include_once("init.php");

			$publication_coverage = $db->query("SELECT * FROM publication_coverage WHERE removed = 0 ORDER BY utime DESC;");
			$num_publication_coverage = count($publication_coverage);
			for($i = 0; $i < $num_publication_coverage; $i++) { 
				//$publication_coverage[$i]['title'] = utf8_encode($publication_coverage[$i]['title']);
				if ($publication_coverage[$i]['title'] == null) {
					$publication_coverage[$i]['title'] = "Untitled Article";
				}
				$publication_coverage[$i]['type'] = "publication";
			}

			$youtuber_coverage = $db->query("SELECT * FROM youtuber_coverage WHERE removed = 0 ORDER BY utime DESC;");
			$youtuber_coverage_coverage = count($youtuber_coverage);
			for($i = 0; $i < $youtuber_coverage_coverage; $i++) { 
				$youtuber_coverage[$i]['type'] = "youtuber";
			}

			$coverage = array_merge($publication_coverage, $youtuber_coverage);

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

		else if ($endpoint == "/keys/list/") 
		{
			$require_login = true;
			include_once("init.php");

			$dolist = true;
			if ($_GET['platform'] != 'steam') {
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
										platform = '" . $_GET['platform'] . "' 
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
					$keysArray = explode("\n", $_GET['keys']);
					for($j = 0; $j < count($keysArray); $j++) {
						if (strlen($keysArray[$j]) != 17) {
							$result = api_error("Steam keys must be in format XXXXX-XXXXX-XXXXX, and one per each line.");
							break;
						}
					}

					// if we get here then the keys are all good! 
					// TODO: should we check for duplicates..?
					for($j = 0; $j < count($keysArray); $j++) {
						$stmt = $db->prepare("INSERT INTO game_key (id, game, platform, keystring, assigned, assignedToType, assignedToTypeId, assignedByUser, assignedByUserTimestamp, createdOn, expiresOn) 
												  			VALUES (NULL, :game, :platform, :keystring, :assigned, :assignedToType, :assignedToTypeId, :assignedByUser, :assignedByUserTimestamp, :createdOn, :expiresOn ); ");
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
						$stmt->execute();
					}

					$result = new stdClass();
					$result->success = true;
					$result->keys = $keysArray;
				}
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

				$stmt = $db->prepare(" UPDATE publication SET name = :name, url = :url, rssfeedurl = :rssfeedurl, twitter = :twitter, " . $twitter_followers_sql . " notes = :notes WHERE id = :id ");
				$stmt->bindValue(":name", $_GET['name'], Database::VARTYPE_STRING);
				$stmt->bindValue(":url", $_GET['url'], Database::VARTYPE_STRING);
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
				$stmt = $db->prepare(" INSERT INTO youtuber (id, 	name,   description, email, channel,  priorities, iconurl,   subscribers, views, notes, twitter,   twitter_followers, 	lastpostedon, removed) 
													VALUES  (NULL, 'Blank', '', 	 	 '', 	:channel, '', 		  '', 		 0, 		  0, 	 '', 	:twitter, :twitter_followers, 	 0, 		  	0);	");
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
				array('name' => 'channel', 'type' => 'alphanumerichyphens'),
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
					$twitter_followers = twitter_countFollowers($_GET['twitter']);
					if ($twitter_followers == "") { $twitter_followers = 0; }
					$twitter_followers_sql = ($twitter_followers > 0)?" twitter_followers = :twitter_followers, ":"";

					$stmt = $db->prepare(" UPDATE youtuber SET 
												channel = :channel,
												name = :name,
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

					$stmt->bindValue(":name", $youtuber['name'], Database::VARTYPE_STRING); 
					$stmt->bindValue(":description", $youtuber['description'], Database::VARTYPE_STRING);
					
					$stmt->bindValue(":iconurl", $youtuber['iconurl'], Database::VARTYPE_STRING);
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
		else if ($endpoint == "/admin/user/add/") 
		{
			$require_login = true;
			include_once("init.php");

			//$query = "INSERT INTO user VALUES (NULL, 'Brett', 'Gwinnell',  'brettgwinnell@hotmail.com', 0, '5f4dcc3b5aa765d61d8327deb882cf99', 1, 'red', 0, 0);";
			//$query = "ALTER TABLE publication ADD COLUMN priorities VARCHAR(255) NOT NULL DEFAULT ''; ";
			//$db->query($query); 
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
		// Chat functionality...
		else if ($endpoint == "/chat/online-users/") { 
			$require_login = true;
			include_once("init.php");

			if (!isset($_SESSION['user']) || !$_SESSION['user']) {
				$result = api_error("You are not logged in.");
			} else {
				// Update current user time.
				$stmt = $db->prepare("UPDATE user SET lastactivity = :lastactivity WHERE id = :id;");
				$stmt->bindValue(":lastactivity", time(), Database::VARTYPE_INTEGER);
				$stmt->bindValue(":id", $_SESSION['user'], Database::VARTYPE_INTEGER);
				$stmt->execute();


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

$var = json_encode($result, true);
if ($var === FALSE) {
	$lasterr = json_last_error();
	echo json_encode(api_error("utf8 error -- could not encode data... " . $lasterr));
	print_r($result);
} else {
	echo $var;
}

//$db->close();
//die();

 
?>