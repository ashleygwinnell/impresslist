<?php
function tryAddYoutubeCoverage($companyId, $youtuberId, $youtuberName, $gameId, $watchedGameId, $title, $url, $thumbnail, $time, $verbose=true) {
	global $db;
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	if ($verbose) {
		echo "Found Coverage!<br/>\n";
	}
	$stmt = $db->prepare("SELECT * FROM youtuber_coverage WHERE url = :url; ");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$existingCoverage = $stmt->query();
	if (count($existingCoverage) == 0) {

		if ($thumbnail == null) {
			$thumbnail = "";
		}

		$title = remove_emoji_from_string($title);
		if ($verbose) {
			echo "Adding coverage from {$youtuberName}<br/>\n";
			echo $title . "<br/>\n";
			echo $url . "<br/>\n";
			echo $time . "<br/>\n";
			echo $thumbnail . "<br/>\n";
			echo "<hr/>\n";
		}
		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO youtuber_coverage (id, youtuber, person, game, watchedgame, url, title, thumbnail, `utime`, thanked, removed)
														VALUES (NULL, :youtuber, NULL, :game, :watchedgame, :url, :title, :thumbnail, :utime, 0, 0 ); ");
		$stmt->bindValue(":youtuber", $youtuberId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$stmt->execute();

		$latest_id = $db->lastInsertRowID();

		// not one of the watched games so much be a proper user-owned game.
		if ($watchedGameId == 0 && $companyId != 0) {
			@email_new_youtube_coverage($companyId, $youtuberName, $url, $time);
			@slack_coverageAlert($companyId, $youtuberName, $title, $url);
			@discord_coverageAlert($companyId, $youtuberName, $title, $url);
		}

		return $latest_id;

	} else {
		if ($verbose) {
			echo $existingCoverage[0]['url'] . "<br/>\n";
			echo "<i>It was already in the database.</i><br/>\n";
		}
		return $existingCoverage[0]['id'];
	}
}

?>
