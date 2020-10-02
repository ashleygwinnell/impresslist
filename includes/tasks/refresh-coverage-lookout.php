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

	$cacheTime = 60*60;
	$doTimelimit = true;
	$timeLimitStr = ($doTimelimit) ? ("AND twitchLastScraped < " . (time()-$cacheTime)):"";

	$lookout_games = $db->query("SELECT * FROM game
									WHERE twitchId != 0 {$timeLimitStr}
									AND coverageTrackPotentials = 1
									AND removed = 0
									ORDER BY twitchLastScraped ASC;");

	$token_details = twitch_getAccessToken();
	$twitch_accessToken = $token_details['access_token'];

	// Twitch lookout looks at game ids and then reports coverage that matches.
	echo "<h1>Twitch</h1>\n";
	for($i = 0; $i < count($lookout_games); ++$i)
	{
		echo "<b>" . $lookout_games[$i]['name'] . "</b>!<br/>\n";

		$twitchId = $lookout_games[$i]['twitchId'];
		if (strlen($twitchId) > 0)
		{
			$videos = twitch_getVideosOfGame($twitchId);
			echo "<b>Videos:</b><br/>";

			if ($videos['data'] != null) {
				for($j = 0; $j < count($videos['data']); $j++) {
					$video = $videos['data'][$j];

					$title = remove_emoji_from_string($video['title']);
					$description = remove_emoji_from_string($video['description']);

					// if (util_is_game_coverage_match($lookout_games[$i], $title, $description))

					$channel = db_singletwitchchannelbytwitchid($db, $video['user_id']);
					tryAddTwitchCoverageUnsure(
						$lookout_games[$i],
						$channel,
						$video['user_id'],
						$video['user_name'],
						$video['id'],  	// video id
						null, 			// clip id
						$video['url'],
						$title,
						$description,
						$video['thumbnail_url'],
						strtotime($video['created_at'])
					);

					// TODO:
					// $video['view_count'];
					// $video['type'];
					// $video['language'];
					// $video['duration'];
				}
			}


			$clips = twitch_getClipsOfGame($twitchId);
			echo "<b>Clips:</b><br/>";

			if ($clips['data'] != null) {
				for($j = 0; $j < count($clips['data']); $j++) {
					$clip = $clips['data'][$j];

					$title = remove_emoji_from_string($clip['title']);
					$description = "";

					$channel = db_singletwitchchannelbytwitchid($db, $clip['broadcaster_id']);\
					tryAddTwitchCoverageUnsure(
						$lookout_games[$i],
						$channel,
						$clip['broadcaster_id'],
						$clip['broadcaster_name'],
						null,  			// video id
						$clip['id'], 	// clip id
						$clip['url'],
						$title,
						$description,
						$clip['thumbnail_url'],
						strtotime($clip['created_at'])
					);

					// TODO:
					// $clip['view_count'];
					// $clip['language'];
				}
			}

			$db->exec("UPDATE game SET twitchLastScraped = " . time() . " WHERE id = " . $lookout_games[$i]['id'] . " ;");
			sleep(1);
		}
		echo "<br/><br/>";
		// die();
	}

	// echo "<b>Done!</b>\n";
	die("temp done");
	die();


	echo "<h1>YouTube</h1>\n";
	for($j = 0; $j < $num_games; $j++) {

		$game = $games[$j];
		$gameName = $game['name'];

		echo "<b>" . $game['name'] . "</b><br/>\n";

		$searches = array(
			array(
				"terms" => "\"" . $gameName . "\" game",
				"type" => "date"
			),
			array(
				"terms" => "\"" . $gameName . "\" game",
				"type" => "relevance"
			),
			array(
				"terms" => $gameName,
				"type" => "date"
			),
			array(
				"terms" => $gameName,
				"type" => "relevance"
			)
		);
		for($k = 0; $k < count($searches); $k++) {
			$searchItem = $searches[$k];
			echo "Searching \"" . $searchItem['terms'] . "\" " . $searchItem['type'] . "<br/>\n";

			$data = youtube_v3_search($searchItem['terms'], $searchItem['type']);
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
				$videoId = $videoDetails['videoId'];
				$videoTitle = remove_emoji_from_string($data['items'][$i]['snippet']['title']);
				$videoDescription = remove_emoji_from_string($data['items'][$i]['snippet']['description']);
				$videoThumbnail = $data['items'][$i]['snippet']['thumbnails']['default']['url'];
				$videoTime = strtotime($data['items'][$i]['snippet']['publishedAt']);

				//echo "video_id: "  . $video_id . "<br/>\n";
				//echo "title: "  . $title . "<br/>\n";
				//echo "description: "  . $description . "<br/>\n";

				$fixedUrl = "https://www.youtube.com/watch?v=".$videoId;

				if (util_is_game_coverage_match($game, $videoTitle, $videoDescription)) {

					// coverage matches here.
					$youtuberDbId = 0;
					$channelmaybe = db_singleyoutubechannelbychannelid($db, $data['items'][$i]['snippet']['channelId']);
					if ($channelmaybe !== FALSE) {
						$youtuberDbId = $channelmaybe['id'];
					}

					coverage_tryAddYoutubeCoverageUnsure(
						$game,
						null,
						// $youtuberDbId,
						$data['items'][$i]['snippet']['channelId'],
						$data['items'][$i]['snippet']['channelTitle'],
						$videoId,
						$videoTitle,
						$videoDescription,
						$videoThumbnail,
						$videoTime,
						true
					);
				}
			}

		}

	}

	$db->exec("UPDATE status SET `value` = " . time() . " WHERE `key` = 'cron_complete_refresh_coverage_lookout' ;");

?>
