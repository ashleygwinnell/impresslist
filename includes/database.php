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