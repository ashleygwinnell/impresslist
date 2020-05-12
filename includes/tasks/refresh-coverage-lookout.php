<?php

	ini_set("allow_url_fopen", "On");

	$startTime = time();
	$require_login = false;
	$require_config = true;
	include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

	$impresslist_verbose = true;

	// Youtubers
	$youtubers = $db->query("SELECT * FROM youtuber WHERE removed = 0 ORDER BY youtubeId ASC;");
	$num_youtubers = count($youtubers);

	// Refresh coverage lookout for all games.
	$games = $db->query("SELECT * FROM game WHERE coverageTrackPotentials = 1 AND removed = 0;");
	$num_games = count($games);


	for($j = 0; $j < $num_games; $j++) {

		$game = $games[$j];
		$gameName = $game['name'];

		echo "<b>" . $game['name'] . "</b><br/>\n";

		$results = array();
		$data = youtube_v3_search("\"" . $gameName . "\" game", "date");
		//print_r($data);
		if ($data === 0) {
			echo "Quota exceeded (probably)<br/><br/>\n";
			continue;
		}
		echo count($data['items']) . " results.<br/>\n";

		for($i = 0; $i < count($data['items']); $i++) {

			$videoDetails = $data['items'][$i]['id'];
			if ($videoDetails['kind'] != "youtube#video") {
				continue;
			}
			$video_id = $videoDetails['videoId'];
			$title = remove_emoji_from_string($data['items'][$i]['snippet']['title']);
			$description = remove_emoji_from_string($data['items'][$i]['snippet']['description']);

			//echo "video_id: "  . $video_id . "<br/>\n";
			//echo "title: "  . $title . "<br/>\n";
			//echo "description: "  . $description . "<br/>\n";

			$fixedUrl = "https://www.youtube.com/watch?v=".$video_id;

			if (util_is_game_coverage_match($game, $title, $description)) {
				// we already have it as potential!
				if (youtuber_coverage_potential_exists($fixedUrl)) {
					continue;
				}
				// we already have it as proper coverage.
				$stmt = $db->prepare("SELECT * FROM youtuber_coverage WHERE url = :url LIMIT 1");
				$stmt->bindValue(":url", $fixedUrl, Database::VARTYPE_STRING);
				$existingCoverage = $stmt->query();
				if (count($existingCoverage) == 1) {
					continue;
				}

				// add it!
				$summary = array(
					"id"   => $video_id,
					"url"  => $fixedUrl,
					"title"   => $data['items'][$i]['snippet']['title'],
					"thumbnail" => $data['items'][$i]['snippet']['thumbnails']['default']['url'],
					"description"  => $data['items'][$i]['snippet']['description'],
					"published_on" => strtotime($data['items'][$i]['snippet']['publishedAt']),
					"channel_id"   => $data['items'][$i]['snippet']['channelId'],
					"channel_title" => $data['items'][$i]['snippet']['channelTitle']
				);
				$potential_id = youtuber_coverage_potential_add($game['id'], $summary);
				echo "<b>Added Potential Coverage:</b> (id " . $potential_id . ") - " . $title . " - " . $fixedUrl . "<br/>\n";
			}
		}

	}


?>
