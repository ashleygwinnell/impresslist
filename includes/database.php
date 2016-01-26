<?php

	
	$db = Database::getInstance();

	function sqlite_epoch($time = 0) {
		return date("Y-m-d H:i:s", $time);
	}
	function db_singleuser($db, $userId) {
		if (!is_numeric($userId)) { return false; }
		$results = $db->query("SELECT * FROM user WHERE id = '" . $userId . "' LIMIT 1;");
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
	function db_singleavailablekeyforgame($db, $gameid, $platform) {
		if (!is_numeric($gameid)) { return false; }
		$rs = $db->query("SELECT * FROM game_key WHERE game = '" . $gameid . "' AND platform = '" . $platform . "' AND assigned = 0 ORDER BY id ASC;");
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
	function db_keysassignedtotype($db, $gameid, $platform, $type, $typeid) {
		$stmt = $db->prepare("SELECT * 
								FROM game_key 
								WHERE game = :game 
									AND platform = :platform
									AND assigned = :assigned
									AND assignedToType = :assignedToType 
									AND assignedToTypeId = :assignedToTypeId
								");
		$stmt->bindValue(":game", $gameid, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":platform", $platform, Database::VARTYPE_STRING);
		$stmt->bindValue(":assigned", 1, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":assignedToType", $type, Database::VARTYPE_STRING);
		$stmt->bindValue(":assignedToTypeId", $typeid, Database::VARTYPE_INTEGER);
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



?>