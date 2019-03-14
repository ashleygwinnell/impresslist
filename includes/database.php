<?php

	try {
		$db = Database::getInstance();
	} catch (Exception $e) {
		if ($impresslist_installed) {
			echo "impress[] is installed but the database cannot be initialised.<br/><br/>";
			Database::printError();
			die($e);
		}
	}

	function sqlite_epoch($time = 0) {
		return date("Y-m-d H:i:s", $time);
	}
	function db_singleuser($db, $userId, $extraFields = array() ) {
		if (!is_numeric($userId)) { return false; }

		$extrasString = implode($extraFields, ',');
		if (strlen($extrasString) > 0) {
			$extrasString = ', '.$extrasString;
		}

		$q = "SELECT user.id, forename, surname, email, color, emailGmailIndex, emailIMAPServer, emailSMTPServer, currentGame, lastactivity, count(email.id) as num_emails, admin, user.removed $extrasString FROM user LEFT JOIN email on email.user_id = user.id where user.removed = 0 and user.id = " . $userId. " group by user.id ;";
		$results = $db->query($q);

		//echo $q;
	//	print_r($results);

		return $results[0];
	}
	function db_singleperson($db, $personId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }
		$rs = $db->query("SELECT * " . $lastcontacted . " FROM person WHERE id = '" . $personId . "' LIMIT 1;");
		return $rs[0];
	}
	function db_singlepersonpublication($db, $personPublicationId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$people = $db->query("SELECT * " . $lastcontacted . " FROM person_publication WHERE id = '" . $personPublicationId . "' LIMIT 1;");
		return $people[0];
	}
	function db_singlepersonyoutubechannel($db, $personYoutubeChannelId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$people = $db->query("SELECT * " . $lastcontacted . " FROM person_youtuber WHERE id = '" . $personYoutubeChannelId . "' LIMIT 1;");
		return $people[0];
	}
	function db_singlepersontwitchchannel($db, $personTwitchChannelId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$people = $db->query("SELECT * " . $lastcontacted . " FROM person_twitchchannel WHERE id = '" . $personTwitchChannelId . "' LIMIT 1;");
		return $people[0];
	}
	function db_singlepublication($db, $publicationId) {
		if (!is_numeric($publicationId)) { return false; }
		$publications = $db->query("SELECT * FROM publication WHERE id = '" . $publicationId . "' LIMIT 1;");
		return $publications[0];
	}
	function db_singleyoutubechannel($db, $youtuberId) {
		if (!is_numeric($youtuberId)) { return false; }
		$youtubeChannels = $db->query("SELECT * FROM youtuber WHERE id = '" . $youtuberId . "' LIMIT 1;");
		return $youtubeChannels[0];
	}
	function db_singletwitchchannel($db, $twitchChannelId) {
		if (!is_numeric($twitchChannelId)) { return false; }
		$twitchChannels = $db->query("SELECT * FROM twitchchannel WHERE id = '" . $twitchChannelId . "' LIMIT 1;");
		return $twitchChannels[0];
	}
	function db_singletwitchchannelbyusername($db, $twitchUsername) {
		if (!is_string($twitchUsername)) { return false; }
		$twitchChannels = $db->query("SELECT * FROM twitchchannel WHERE twitchUsername = '" . $twitchUsername . "' LIMIT 1;");
		return $twitchChannels[0];
	}
	function db_singlemailoutsimple($db, $mailoutId) {
		if (!is_numeric($mailoutId)) { return false; }
		$mailouts = $db->query("SELECT * FROM emailcampaignsimple WHERE id = '" . $mailoutId . "' LIMIT 1;");
		return $mailouts[0];
	}
	function db_singlegame($db, $gameId) {
		if (!is_numeric($gameId)) { return false; }
		$rs = $db->query("SELECT * FROM game WHERE id = '" . $gameId . "' LIMIT 1;");
		return $rs[0];
	}
	function db_singleavailablekeyforgame($db, $gameid, $platform, $subplatform) {
		if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM game_key WHERE game = '" . $gameid . "' AND platform = '" . $platform . "' AND subplatform = '" . $subplatform . "' AND assigned = 0 AND removed = 0 ORDER BY id ASC;");
		return $rs[0];
	}
	function db_singleOAuthTwitter($db, $twitterAccId) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_twitteracc WHERE id = '" . $twitterAccId . "' AND removed = 0  LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthTwitterById($db, $id) {
		if (!is_numeric($id)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_twitteracc WHERE id = " . $id . " AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthTwitterByHandle($db, $twitterHandle) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_twitteracc WHERE twitter_handle = '" . $twitterHandle . "' AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthFacebookByFBId($db, $facebookId) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_facebookacc WHERE facebook_id = '" . $facebookId . "' AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthFacebookByUserId($db, $userId) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_facebookacc WHERE user = '" . $userId . "' AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthFacebookPageById($db, $pageId) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_facebookpage WHERE id = '" . $pageId . "' AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleOAuthFacebookPageByFBPId($db, $facebookPageId) {
		//if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM oauth_facebookpage WHERE page_id = '" . $facebookPageId . "' AND removed = 0 LIMIT 1;");
		return $rs[0];
	}
	function db_singleSocialQueueItem($db, $id) {
		if (!is_numeric($id)) { return false; }
		$rs = $db->query("SELECT * FROM socialqueue WHERE id = " . $id . " LIMIT 1;");
		return $rs[0];
	}
	function db_keysassignedtotype($db, $gameid, $platform, $subplatform, $type, $typeid) {
		$stmt = $db->prepare("SELECT *
								FROM game_key
								WHERE game = :game
									AND platform = :platform
									AND subplatform = :subplatform
									AND assigned = :assigned
									AND assignedToType = :assignedToType
									AND assignedToTypeId = :assignedToTypeId
									AND removed = :removed
								");
		$stmt->bindValue(":game", $gameid, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":platform", $platform, Database::VARTYPE_STRING);
		$stmt->bindValue(":subplatform", $subplatform, Database::VARTYPE_STRING);
		$stmt->bindValue(":assigned", 1, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":assignedToType", $type, Database::VARTYPE_STRING);
		$stmt->bindValue(":assignedToTypeId", $typeid, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
		return $stmt->query();
	}
	function db_defaultPrioritiesString($db) {
		$string = "";
		$count = 0;
		$results = $db->query("SELECT * FROM game;");
		foreach ($results as $result) {
			if ($count > 0) {
				$string .= ",";
			}
			$string .= $result['id'] . "=0";
			$count += 1;
		}
		return $string;
	}

	function db_reset($db) {
		$sql = "DROP TABLE email;";
		$db->exec($sql);

		$sql = "DROP TABLE emailcampaignsimple;";
		$db->exec($sql);

		$sql = "DROP TABLE emailqueue;";
		$db->exec($sql);

		$sql = "DROP TABLE game;";
		$db->exec($sql);

		$sql = "DROP TABLE game_key;";
		$db->exec($sql);

		$sql = "DROP TABLE oauth_facebookacc;";
		$db->exec($sql);

		$sql = "DROP TABLE oauth_facebookpage;";
		$db->exec($sql);

		$sql = "DROP TABLE oauth_twitteracc;";
		$db->exec($sql);

		$sql = "DROP TABLE person;";
		$db->exec($sql);

		$sql = "DROP TABLE person_publication;";
		$db->exec($sql);

		$sql = "DROP TABLE person_youtuber;";
		$db->exec($sql);

		$sql = "DROP TABLE publication;";
		$db->exec($sql);

		$sql = "DROP TABLE publication_coverage;";
		$db->exec($sql);

		$sql = "DROP TABLE settings;";
		$db->exec($sql);

		$sql = "DROP TABLE socialqueue;";
		$db->exec($sql);

		$sql = "DROP TABLE user;";
		$db->exec($sql);

		$sql = "DROP TABLE youtuber;";
		$db->exec($sql);

		$sql = "DROP TABLE youtuber_coverage;";
		$db->exec($sql);
	}

	// Keep these in alphabetical order please.
	function db_install($db)
	{
		// keywords
		$autoincrement = "AUTOINCREMENT";
		$blobTextDefaultToZero = " DEFAULT '0' ";
		$sqlEngineAndCharset = '';
		$defaultNull = "0";
		if ($db->type == Database::TYPE_MYSQL) {
			$autoincrement = "AUTO_INCREMENT";
			$blobTextDefaultToZero = "";
			$sqlEngineAndCharset = ' ENGINE=InnoDB DEFAULT CHARSET=utf8 ';
			$defaultNull = "NULL";
		}

		// Emails
		$sql = "CREATE TABLE IF NOT EXISTS email (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					user_id INTEGER NOT NULL,
					person_id INTEGER NOT NULL,
					game_id INTEGER DEFAULT {$defaultNull},
					utime INTEGER NOT NULL,
					from_email VARCHAR(255) NOT NULL,
					to_email VARCHAR(255) NOT NULL,
					subject varchar(255) NOT NULL,
					contents text NOT NULL,
					unmatchedrecipient INTEGER NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Email camapaign system (simple)
		$sql = "CREATE TABLE IF NOT EXISTS emailcampaignsimple (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					game_id INTEGER DEFAULT {$defaultNull},
					name VARCHAR(255) NOT NULL,
					subject VARCHAR(255) NOT NULL,
					recipients TEXT NOT NULL,
					markdown TEXT NOT NULL,
					`timestamp` INTEGER NOT NULL,
					user INTEGER NOT NULL,
					ready INTEGER NOT NULL DEFAULT 0,
					sent INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;" ;
		$db->exec($sql);

		// Email queue system (notifications)
		$sql = "CREATE TABLE IF NOT EXISTS emailqueue (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					subject VARCHAR(255) NOT NULL,
					to_address VARCHAR(255) NOT NULL,
					headers TEXT NOT NULL,
					message TEXT NOT NULL,
					`timestamp` INTEGER NOT NULL,
					sent INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Game / project data
		$sql = "CREATE TABLE IF NOT EXISTS game (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					name VARCHAR(255),
					nameuniq VARCHAR(255),
					iconurl VARCHAR(255) NOT NULL,
					keywords TEXT NOT NULL,
					twitchId INTEGER DEFAULT {$defaultNull},
					twitchLastScraped INTEGER DEFAULT {$defaultNull}
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Game keys
		$sql = "CREATE TABLE IF NOT EXISTS game_key (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					game INTEGER NOT NULL,
					platform VARCHAR(16) NOT NULL,
					keystring VARCHAR(255) NOT NULL,
					assigned INTEGER NOT NULL DEFAULT 0,
					assignedToType VARCHAR(20) NOT NULL,
					assignedToTypeId INTEGER NOT NULL,
					assignedByUser INTEGER NOT NULL,
					assignedByUserTimestamp INTEGER NOT NULL,
					createdOn INTEGER NOT NULL,
					expiresOn INTEGER NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0,
					removedByUser INTEGER,
					removedByUserTimestamp INTEGER
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql); // TODO: add indexes?

		// Facebook accounts
		$sql = "CREATE TABLE IF NOT EXISTS oauth_facebookacc (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					user INTEGER NOT NULL,
					facebook_id VARCHAR(255) NOT NULL,
					facebook_name VARCHAR(255) NOT NULL,
					facebook_image TEXT NOT NULL,
					facebook_accessToken TEXT NOT NULL,
					lastSync INTEGER NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Facebook pages
		$sql = "CREATE TABLE IF NOT EXISTS oauth_facebookpage (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					page_id VARCHAR(255) NOT NULL,
					page_name VARCHAR(255) NOT NULL,
					page_image TEXT NOT NULL,
					page_accessToken TEXT NOT NULL,
					lastSync INTEGER NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Twitter accounts
		$sql = "CREATE TABLE IF NOT EXISTS oauth_twitteracc (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					twitter_id TEXT NOT NULL,
					twitter_name TEXT NOT NULL,
					twitter_handle TEXT NOT NULL,
					twitter_image TEXT NOT NULL,
					oauth_key TEXT NOT NULL,
					oauth_secret TEXT NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// People
		$sql = "CREATE TABLE IF NOT EXISTS person (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					firstname VARCHAR(255) NOT NULL,
					surnames VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL,
					priorities VARCHAR(255) NOT NULL,
					twitter VARCHAR(255) NOT NULL,
					twitter_followers INTEGER NOT NULL DEFAULT 0,
					twitter_updatedon INTEGER NOT NULL DEFAULT 0,
					notes TEXT NOT NULL,
					lang VARCHAR(30) NOT NULL,
					country VARCHAR(2) NOT NULL DEFAULT '',
					lastcontacted INTEGER NOT NULL,
					lastcontactedby INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0,
					assigned INTEGER NOT NULL DEFAULT 0,
					outofdate INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// People - publications
		$sql = "CREATE TABLE IF NOT EXISTS person_publication (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					person INTEGER NOT NULL,
					publication INTEGER NOT NULL,
					email VARCHAR(255) NOT NULL,
					lastcontacted INTEGER NOT NULL,
					lastcontactedby INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// People - youtube channels
		$sql = "CREATE TABLE IF NOT EXISTS person_youtuber (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					person INTEGER NOT NULL,
					youtuber INTEGER NOT NULL
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS person_twitchchannel (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					person INTEGER NOT NULL,
					twitchchannel INTEGER NOT NULL
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// create publications
		$sql = "CREATE TABLE IF NOT EXISTS publication (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					name VARCHAR(255) NOT NULL,
					url VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL,
					iconurl VARCHAR(255) NOT NULL,
					iconurl_updatedon INTEGER NOT NULL DEFAULT 0,
					rssfeedurl VARCHAR(255) NOT NULL,
					priorities VARCHAR(255) NOT NULL,
					twitter VARCHAR(255) NOT NULL,
					twitter_followers INTEGER NOT NULL,
					twitter_updatedon INTEGER NOT NULL DEFAULT 0,
					notes TEXT NOT NULL,
					lang VARCHAR(30) NOT NULL,
					country VARCHAR(2) NOT NULL DEFAULT '',
					lastpostedon INTEGER NOT NULL,
					lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0,
					lastscrapedon INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Coverage from traditional games press.
		$sql = "CREATE TABLE IF NOT EXISTS publication_coverage (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					publication INTEGER DEFAULT {$defaultNull},
					person INTEGER DEFAULT {$defaultNull},
					game INTEGER DEFAULT {$defaultNull},
					watchedgame INTEGER DEFAULT {$defaultNull},
					url VARCHAR(255) NOT NULL,
					title TEXT NOT NULL,
					utime INTEGER NOT NULL DEFAULT 0,
					thanked INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS settings (
					`key` VARCHAR(255) PRIMARY KEY NOT NULL,
					`value` TEXT
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Social timeline / queue.
		$sql = "CREATE TABLE IF NOT EXISTS socialqueue (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					type VARCHAR(255) NOT NULL,
					typedata TEXT NOT NULL,
					user_id INTEGER NOT NULL,
					`timestamp` INTEGER NOT NULL,
					ready INTEGER NOT NULL DEFAULT 0,
					sent INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Users
		$sql = "CREATE TABLE IF NOT EXISTS user (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					forename VARCHAR(255) NOT NULL,
					surname VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL,
					emailGmailIndex INTEGER NOT NULL DEFAULT 0,
					emailSMTPServer VARCHAR(255) NOT NULL,
					emailIMAPServer VARCHAR(255) NOT NULL,
					emailIMAPPassword VARCHAR(255) NOT NULL,
					emailIMAPPasswordSalt VARCHAR(255) NOT NULL,
					emailIMAPPasswordIV VARCHAR(255) NOT NULL,
					password VARCHAR(32) NOT NULL,
					passwordVersion INTEGER NOT NULL DEFAULT 1,
					currentGame INTEGER NOT NULL,
					coverageNotifications INTEGER NOT NULL DEFAULT 1,
					color VARCHAR(10) NOT NULL DEFAULT '#000000',
					admin INTEGER NOT NULL DEFAULT 0,
					lastactivity INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Watched Games
		$sql = "CREATE TABLE IF NOT EXISTS watchedgame (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					name VARCHAR(255) NOT NULL,
					keywords TEXT NOT NULL,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);
		$sql = "CREATE TABLE IF NOT EXISTS game_watchedgame (
					game_id INTEGER NOT NULL,
					watchedgame_id INTEGER NOT NULL
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);
		$sql = "ALTER TABLE `game_watchedgame` ADD PRIMARY KEY( `game_id`, `watchedgame_id`) ";
		$db->exec($sql);


		// Youtuber profiles.
		$sql = "CREATE TABLE IF NOT EXISTS youtuber (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					youtubeId VARCHAR(255) NOT NULL,
					youtubeUploadsPlaylistId VARCHAR(255) NOT NULL,
					name VARCHAR(255) NOT NULL,
					name_override VARCHAR(255) NOT NULL,
					description TEXT NOT NULL,
					email VARCHAR(255) NOT NULL DEFAULT '',
					priorities VARCHAR(255) NOT NULL,
					channel VARCHAR(255) NOT NULL,
					iconurl VARCHAR(255) NOT NULL,
					subscribers TEXT NOT NULL {$blobTextDefaultToZero},
					views TEXT NOT NULL {$blobTextDefaultToZero},
					videos INTEGER NOT NULL,
					twitter VARCHAR(255) NOT NULL DEFAULT '',
					twitter_followers INTEGER NOT NULL DEFAULT 0,
					twitter_updatedon INTEGER NOT NULL DEFAULT 0,
					notes TEXT NOT NULL,
					lang VARCHAR(30) NOT NULL,
					country VARCHAR(2) NOT NULL DEFAULT '',
					lastpostedon INTEGER NOT NULL,
					lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0,
					lastscrapedon INTEGER NOT NULL DEFAULT 0,
					lastcontacted INTEGER NOT NULL DEFAULT 0,
					lastcontactedby INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Youtuber coverage.
		$sql = "CREATE TABLE IF NOT EXISTS youtuber_coverage (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					youtuber INTEGER DEFAULT {$defaultNull},
					person INTEGER DEFAULT {$defaultNull},
					game INTEGER DEFAULT {$defaultNull},
					watchedgame INTEGER DEFAULT {$defaultNull},
					url VARCHAR(255) NOT NULL,
					title TEXT NOT NULL,
					thumbnail TEXT NOT NULL,
					utime INTEGER NOT NULL DEFAULT 0,
					thanked INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Twitch channel
		$sql = "CREATE TABLE IF NOT EXISTS twitchchannel (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					twitchId VARCHAR(255) NOT NULL,
					twitchDescription TEXT NOT NULL,
					twitchBroadcasterType VARCHAR(30) NOT NULL,
					twitchProfileImageUrl VARCHAR(255) NOT NULL,
					twitchOfflineImageUrl VARCHAR(255) NOT NULL,
					twitchUsername VARCHAR(255) NOT NULL,
					name VARCHAR(255) NOT NULL,
					email VARCHAR(255) NOT NULL DEFAULT '',
					priorities VARCHAR(255) NOT NULL,
					subscribers TEXT NOT NULL {$blobTextDefaultToZero},
					views TEXT NOT NULL {$blobTextDefaultToZero},
					twitter VARCHAR(255) NOT NULL DEFAULT '',
					twitter_followers INTEGER NOT NULL DEFAULT 0,
					twitter_updatedon INTEGER NOT NULL DEFAULT 0,
					notes TEXT NOT NULL,
					lang VARCHAR(30) NOT NULL,
					lastpostedon INTEGER NOT NULL,
					lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0,
					lastscrapedon INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS twitchchannel_coverage (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					twitchchannel INTEGER DEFAULT {$defaultNull},
					twitchVideoId VARCHAR(255) DEFAULT {$defaultNull},
					twitchClipId VARCHAR(255) DEFAULT {$defaultNull},
					twitchChannelId INTEGER DEFAULT {$defaultNull},
					game INTEGER DEFAULT {$defaultNull},
					url VARCHAR(255) NOT NULL,
					title VARCHAR(255) NOT NULL,
					description TEXT NOT NULL,
					thumbnail TEXT NOT NULL,
					utime INTEGER NOT NULL DEFAULT 0,
					thanked INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		$sql = "CREATE TABLE IF NOT EXISTS twitter_directmessage (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					dm_id VARCHAR(50) NOT NULL,
					from_twitter_id VARCHAR(50) NOT NULL,
					from_twitter_username VARCHAR(50) NOT NULL,
					to_twitter_id VARCHAR(50) NOT NULL,
					to_twitter_username VARCHAR(50) NOT NULL,
					message_raw TEXT NOT NULL,
					message TEXT NOT NULL,
					createdon INTEGER NOT NULL
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);


		// Settings
		$db->exec("INSERT IGNORE INTO settings VALUES ('company_name', 'Company Name'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('company_addressLine', 'Company Name, 1 Tree Hill, City, Country.'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('company_emailAddress', 'contact@yourwebdomain.com'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('company_twitter', 'http://twitter.com/company_name'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('company_facebook', 'http://facebook.com/company_name'); ");

		$db->exec("INSERT IGNORE INTO settings VALUES ('cacheType', '" . Cache::TYPE_NONE . "'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('memcacheServer', 'localhost'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('memcachePort', '11211'); ");

		$db->exec("INSERT IGNORE INTO settings VALUES ('auto_backup_email', ''); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('auto_backup_frequency', 0); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('manual_backup_lastbackedupon', 0); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('todolist', ''); ");

		global $impresslist_version;
		$db->exec("INSERT IGNORE INTO settings VALUES ('version', '" . $impresslist_version . "'); ");

		// Youtube settings
		$db->exec("INSERT IGNORE INTO settings VALUES ('youtube_apiKey', 'youtube_api_key'); ");

		// Twitter API settings
		$db->exec("INSERT IGNORE INTO settings VALUES ('twitter_configuration', '{}'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('twitter_consumerKey', 'consumer_key'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('twitter_consumerSecret', 'consumer_secret'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('twitter_oauthToken', 'oauth_token'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('twitter_oauthSecret', 'oauth_secret'); ");

		// Facebook API settings - https://developers.facebook.com
		$db->exec("INSERT IGNORE INTO settings VALUES ('facebook_appId', 'app_id'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('facebook_appSecret', 'app_secret'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('facebook_apiVersion', 'api_version'); ");

		// Slack integration settings
		$db->exec("INSERT IGNORE INTO settings VALUES ('slack_enabled', 'false'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES ('slack_apiUrl', 'https://hooks.slack.com/services/GENERATE/THIS/URL'); ");

		return true;
	}

	function db_testdata($db) {
		// Add some test data, who wants in here?
	}

	function db_getSettings($db) {
		if ($db == null) { return array(); }

		$keyedSettings = array();
		$settings = $db->query("SELECT * FROM settings;");
		foreach($settings as $setting) {
			$keyedSettings[$setting['key']] = $setting['value'];
		}
		return $keyedSettings;
	}


?>
