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
	function db_singlecompany($db, $companyId, $extraFields = array() ) {
		if (!is_numeric($companyId)) { return false; }
		$extrasString = implode($extraFields, ',');
		if (strlen($extrasString) > 0) {
			$extrasString = ', '.$extrasString;
		}
		$q = "SELECT company.id, name $extrasString FROM company where removed = 0 and company.id = :company_id group by company.id ;";
		$stmt = $db->prepare($q);
		$stmt->bindValue(":company_id", $companyId, Database::VARTYPE_INTEGER);
		$results = $stmt->query();
		return $results[0];
	}
	function db_singleuser($db, $userId, $extraFields = array() ) {
		if (!is_numeric($userId)) { return false; }

		$extrasString = implode($extraFields, ',');
		if (strlen($extrasString) > 0) {
			$extrasString = ', '.$extrasString;
		}

		$q = "SELECT user.id, company, forename, surname, email, color, emailGmailIndex, emailIMAPServer, emailSMTPServer, currentAudience, currentGame, lastactivity, count(email.id) as num_emails, admin, superadmin, user.removed $extrasString FROM user LEFT JOIN email on email.user_id = user.id where user.removed = 0 and user.id = :user_id group by user.id ;";
		$stmt = $db->prepare($q);
		$stmt->bindValue(":user_id", $userId, Database::VARTYPE_INTEGER);
		$results = $stmt->query();

		//echo $q;
	//	print_r($results);

		return $results[0];
	}
	function db_singleperson($db, $personId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }
		$stmt = $db->prepare("SELECT person.*, audience.company " . $lastcontacted . "
								FROM person
								JOIN audience on person.audience = audience.id
								WHERE person.id = :person_id
								LIMIT 1;");
		$stmt->bindValue(":person_id", $personId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singlepersonpublication($db, $personPublicationId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$stmt = $db->prepare("SELECT person_publication.* " . $lastcontacted . "
								FROM person_publication
								WHERE id = :id
								LIMIT 1;");
		$stmt->bindValue(":id", $personPublicationId, Database::VARTYPE_INTEGER);
		$people = $stmt->query();
		return $people[0];
	}
	function db_singlepersonyoutubechannel($db, $personYoutubeChannelId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$stmt = $db->prepare("SELECT person_youtuber.* " . $lastcontacted . "
								FROM person_youtuber
								WHERE id = :id
								LIMIT 1;");
		$stmt->bindValue(":id", $personYoutubeChannelId, Database::VARTYPE_INTEGER);
		$people = $stmt->query();
		return $people[0];
	}
	function db_singlepersontwitchchannel($db, $personTwitchChannelId) {
		$lastcontacted = ", strftime('%s', lastcontacted) as lastcontacted_timestamp ";
		if ($db->type == Database::TYPE_MYSQL) { $lastcontacted = ""; }

		$stmt = $db->prepare("SELECT person_twitchchannel.* " . $lastcontacted . "
								FROM person_twitchchannel
								WHERE id = :id
								LIMIT 1;");
		$stmt->bindValue(":id", $personTwitchChannelId, Database::VARTYPE_INTEGER);
		$people = $stmt->query();
		return $people[0];
	}
	function db_singlepublication($db, $publicationId) {
		if (!is_numeric($publicationId)) { return false; }
		$stmt = $db->prepare("SELECT publication.*, audience.company
								FROM publication
								JOIN audience on publication.audience = audience.id
								WHERE publication.id = :publication_id
								LIMIT 1;");
		$stmt->bindValue(":publication_id", $publicationId, Database::VARTYPE_INTEGER);
		$publications = $stmt->query();
		return $publications[0];
	}
	function db_singleyoutubechannel($db, $youtuberId) {
		if (!is_numeric($youtuberId)) { return false; }
		$stmt = $db->prepare("SELECT youtuber.*, audience.company
								FROM youtuber
								JOIN audience on youtuber.audience = audience.id
								WHERE youtuber.id = :youtuber_id
								LIMIT 1;");
		$stmt->bindValue(":youtuber_id", $youtuberId, Database::VARTYPE_INTEGER);
		$youtubeChannels = $stmt->query();
		return $youtubeChannels[0];
	}
	function db_singletwitchchannel($db, $twitchChannelId) {
		if (!is_numeric($twitchChannelId)) { return false; }
		$stmt = $db->prepare("SELECT twitchchannel.*, audience.company
									FROM twitchchannel
									JOIN audience on twitchchannel.audience = audience.id
									WHERE twitchchannel.id = :twitchchannel_id
									LIMIT 1;");
		$stmt->bindValue(":twitchchannel_id", $twitchChannelId, Database::VARTYPE_INTEGER);
		$twitchChannels = $stmt->query();
		return $twitchChannels[0];
	}
	function db_singletwitchchannelbyusername($db, $twitchUsername) {
		if (!is_string($twitchUsername)) { return false; }
		$stmt = $db->prepare("SELECT twitchchannel.*, audience.company
								FROM twitchchannel
								JOIN audience on twitchchannel.audience = audience.id
								WHERE twitchchannel.twitchUsername = :username
								LIMIT 1;");
		$stmt->bindValue(":username", $twitchUsername, Database::VARTYPE_STRING);
		$twitchChannels = $stmt->query();
		return $twitchChannels[0];
	}
	function db_singlemailoutsimple($db, $mailoutId) {
		if (!is_numeric($mailoutId)) { return false; }
		$stmt = $db->prepare("SELECT *
								FROM emailcampaignsimple
								WHERE emailcampaignsimple.id = :mailout_id
								LIMIT 1;");
		$stmt->bindValue(":mailout_id", $mailoutId, Database::VARTYPE_INTEGER);
		$mailouts = $stmt->query();
		return $mailouts[0];
	}
	function db_singleaudience($db, $audienceId) {
		if (!is_numeric($audienceId)) { return false; }
		$stmt = $db->prepare("SELECT audience.* FROM audience WHERE audience.id = :audience_id LIMIT 1;");
		$stmt->bindValue(":audience_id", $audienceId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singlegame($db, $gameId) {
		if (!is_numeric($gameId)) { return false; }
		$stmt = $db->prepare("SELECT game.* FROM game WHERE game.id = :game_id LIMIT 1;");
		$stmt->bindValue(":game_id", $gameId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleavailablekeyforgame($db, $gameid, $platform, $subplatform) {
		if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM game_key WHERE game = :game_id AND platform = :platform AND subplatform = :subplatform AND game_key.assigned = 0 AND game_key.removed = 0 ORDER BY game_key.id ASC;");
		$stmt->bindValue(":game_id", $gameid, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":platform", $platform, Database::VARTYPE_STRING);
		$stmt->bindValue(":subplatform", $subplatform, Database::VARTYPE_STRING);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthTwitter($db, $twitterAccId) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_twitteracc WHERE id = :id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":id", $twitterAccId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthTwitterById($db, $id) {
		if (!is_numeric($id)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_twitteracc WHERE id = :id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":id", $id, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthTwitterByHandle($db, $twitterHandle) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_twitteracc WHERE twitter_handle = :handle AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":handle", $twitterHandle, Database::VARTYPE_STRING);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthFacebookByFBId($db, $facebookId) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_facebookacc WHERE facebook_id = :facebook_id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":facebook_id", $facebookId, Database::VARTYPE_STRING);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthFacebookByUserId($db, $userId) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_facebookacc WHERE user = :user_id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":user_id", $userId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthFacebookPageById($db, $pageId) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_facebookpage WHERE id = :id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":id", $pageId, Database::VARTYPE_INTEGER);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleOAuthFacebookPageByFBPId($db, $facebookPageId) {
		//if (!is_numeric($gameid)) { return false; }
		$stmt = $db->prepare("SELECT * FROM oauth_facebookpage WHERE page_id = :page_id AND removed = 0 LIMIT 1;");
		$stmt->bindValue(":page_id", $facebookPageId, Database::VARTYPE_STRING);
		$rs = $stmt->query();
		return $rs[0];
	}
	function db_singleSocialQueueItem($db, $id) {
		if (!is_numeric($id)) { return false; }
		$rs = $db->prepare("SELECT * FROM socialqueue WHERE id = :id LIMIT 1;");
		$stmt->bindValue(":id", $id, Database::VARTYPE_INTEGER);
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
		$sql = "DROP TABLE audience;";
		$db->exec($sql);

		$sql = "DROP TABLE cache_external_twitteracc;";
		$db->exec($sql);

		$sql = "DROP TABLE company;";
		$db->exec($sql);

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

		$sql = "DROP TABLE person_twitchchannel;";
		$db->exec($sql);

		$sql = "DROP TABLE person_youtuber;";
		$db->exec($sql);

		$sql = "DROP TABLE podcast;";
		$db->exec($sql);

		$sql = "DROP TABLE publication;";
		$db->exec($sql);

		$sql = "DROP TABLE publication_coverage;";
		$db->exec($sql);

		$sql = "DROP TABLE settings;";
		$db->exec($sql);

		$sql = "DROP TABLE socialqueue;";
		$db->exec($sql);

		$sql = "DROP TABLE twitchchannel;";
		$db->exec($sql);

		$sql = "DROP TABLE twitchchannel_coverage;";
		$db->exec($sql);

		$sql = "DROP TABLE twitter_directmessage;";
		$db->exec($sql);

		$sql = "DROP TABLE watchedgame;";
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
		$tinyint = "INTEGER";
		if ($db->type == Database::TYPE_MYSQL) {
			$autoincrement = "AUTO_INCREMENT";
			$blobTextDefaultToZero = "";
			$sqlEngineAndCharset = ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
			$defaultNull = "NULL";
			$tinyint = "tinyint(4)";
		}

		// Audiences
		$sql = "CREATE TABLE IF NOT EXISTS audience (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					company INTEGER NOT NULL,
					name VARCHAR(255) NOT NULL,
					removed {$tinyint} NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);
		$db->exec("INSERT IGNORE INTO `audience` (`id`, `company`, `name`, `removed`) VALUES (1, 1, 'Audience', 0); ");

		// Companies
		$sql = "CREATE TABLE IF NOT EXISTS company (
					`id` INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					`name` varchar(50) NOT NULL,
					`keywords` text NOT NULL,
					`address` varchar(255) NOT NULL,
					`email` varchar(255) NOT NULL,
					`twitter` varchar(30) NOT NULL,
					`facebook` varchar(30) NOT NULL,
					`discord_enabled` {$tinyint} NOT NULL DEFAULT 0,
					`discord_webhookId` varchar(255) NOT NULL,
					`discord_webhookToken` varchar(255) NOT NULL,
					`createdon` INTEGER NOT NULL,
					`removed` {$tinyint} NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);
		$db->exec("INSERT IGNORE INTO `company` (`id`, `name`, `keywords`, `address`, `email`, `twitter`, `facebook`, `discord_enabled`, `discord_webhookId`, `discord_webhookToken`, `createdon`, `removed`) VALUES
												 (1, 'Company Name', 'Company Name', 'Your full address line.', 'hello@yourdomain.com', 'twitter', 'facebook', 0, '', '', 0, 0);");

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
					company INTEGER NOT NULL DEFAULT 1,
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
					company INTEGER NOT NULL DEFAULT 1,
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
					company INTEGER NOT NULL DEFAULT 1,
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
					company INTEGER NOT NULL DEFAULT 1,
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
					company INTEGER NOT NULL DEFAULT 1,
					twitter_id TEXT NOT NULL,
					twitter_name TEXT NOT NULL,
					twitter_handle TEXT NOT NULL,
					twitter_image TEXT NOT NULL,
					oauth_key TEXT NOT NULL,
					oauth_secret TEXT NOT NULL,
					twitter_friends TEXT NOT NULL,
					twitter_followers TEXT NOT NULL,
					lastscrapedon INTEGER NOT NULL DEFAULT 0,
					removed INTEGER NOT NULL DEFAULT 0
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// People
		$sql = "CREATE TABLE IF NOT EXISTS person (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					audience INTEGER NOT NULL DEFAULT 1,
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
					tags TEXT NOT NULL,
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
					audience INTEGER NOT NULL DEFAULT 1,
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
					tags TEXT NOT NULL,
					lastpostedon INTEGER NOT NULL,
					lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
					lastcontacted INTEGER NULL DEFAULT NULL,
					lastcontactedby INTEGER NULL DEFAULT NULL,
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
					`company` INTEGER NOT NULL DEFAULT 1,
					`key` VARCHAR(255) PRIMARY KEY NOT NULL,
					`value` TEXT
				) {$sqlEngineAndCharset} ;";
		$db->exec($sql);

		// Social timeline / queue.
		$sql = "CREATE TABLE IF NOT EXISTS socialqueue (
					id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
					`company` INTEGER NOT NULL DEFAULT 1,
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
					`company` INTEGER NOT NULL DEFAULT 1,
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
					currentAudience INTEGER NOT NULL,
					currentGame INTEGER NOT NULL,
					coverageNotifications INTEGER NOT NULL DEFAULT 1,
					color VARCHAR(10) NOT NULL DEFAULT '#000000',
					admin INTEGER NOT NULL DEFAULT 0,
					superadmin INTEGER NOT NULL DEFAULT 0,
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
					audience INTEGER NOT NULL DEFAULT 1,
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
					tags TEXT NOT NULL,
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
					audience INTEGER NOT NULL DEFAULT 1,
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
					country VARCHAR(2) NOT NULL DEFAULT '',
					tags TEXT NOT NULL,
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
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'company_name', 'Company Name'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'company_addressLine', 'Company Name, 1 Tree Hill, City, Country.'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'company_emailAddress', 'contact@yourwebdomain.com'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'company_twitter', 'http://twitter.com/company_name'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'company_facebook', 'http://facebook.com/company_name'); ");

		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'cacheType', '" . Cache::TYPE_NONE . "'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'memcacheServer', 'localhost'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'memcachePort', '11211'); ");

		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'auto_backup_email', ''); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'auto_backup_frequency', 0); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'manual_backup_lastbackedupon', 0); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'todolist', ''); ");

		global $impresslist_version;
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'version', '" . $impresslist_version . "'); ");

		// Youtube settings
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'youtube_apiKey', 'youtube_api_key'); ");

		// Twitter API settings
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'twitter_configuration', '{}'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'twitter_consumerKey', 'consumer_key'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'twitter_consumerSecret', 'consumer_secret'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'twitter_oauthToken', 'oauth_token'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'twitter_oauthSecret', 'oauth_secret'); ");

		// Facebook API settings - https://developers.facebook.com
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'facebook_appId', 'app_id'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'facebook_appSecret', 'app_secret'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'facebook_apiVersion', 'api_version'); ");

		// Slack integration settings
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'slack_enabled', 'false'); ");
		$db->exec("INSERT IGNORE INTO settings VALUES (1, 'slack_apiUrl', 'https://hooks.slack.com/services/GENERATE/THIS/URL'); ");

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
