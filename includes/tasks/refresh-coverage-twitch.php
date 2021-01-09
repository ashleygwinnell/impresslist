<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

// Games
$cacheTime = 60*60;
$doTimelimit = true;
$timeLimitStr = ($doTimelimit) ? ("AND twitchLastScraped < " . (time()-$cacheTime)):"";

$all_games = $db->query("	SELECT * FROM game
								WHERE twitchId != 0 {$timeLimitStr}
								AND removed = 0
								ORDER BY twitchLastScraped ASC;");

$streamers = $db->query("SELECT * FROM twitchchannel WHERE twitchId != 0 AND lastscrapedon < " . (time()-$cacheTime) . " AND removed = 0 ORDER BY RAND() ASC LIMIT 30;");
$num_streamers = count($streamers);

$token_details = twitch_getAccessToken();
$twitch_accessToken = $token_details['access_token'];


// EXISTING TWITCH CHANNELS.
// Search by Streamers + title/description
echo "<b>Streamers</b><br/>\n";
$ids = [];
for($i = 0; $i < count($streamers); $i++) {
	$streamer = $streamers[$i];
	$name = $streamer['twitchUsername'];
	if (strlen($name) == 0) {
		continue;
	}
	$ids[] = $streamer['twitchId'];

	echo "" . $name . "!<br/>\n";

	$lastUploadedOn = 0;

	$videos = twitch_getVideosForUser($streamer['twitchId']);
	if ($videos) {
		$items = $videos['data'];
		for($j = 0; $j < count($items); $j++) {
			$item = $items[$j];

			$published_at  = strtotime($item['published_at']);
			if ($published_at > $lastUploadedOn) {
				$lastUploadedOn = $published_at;
			}

			foreach ($all_games as $game) {
				$title = remove_emoji_from_string($item['title']);
				$description = remove_emoji_from_string($item['description']);
				if (util_is_game_coverage_match($game, $title, $description))
				{
					// potential OR proper depending on game settings.
					tryAddTwitchCoverageUnsure(
						$game,								// $game,
						$streamer,							// $dbchannel,
						$item['user_id'],					// $twitchChannelId,
						$item['user_name'],					// $twitchChannelName,
						$item['id'],  // video id			// $twitchVideoId,
						null, 		  // clip id			// $twitchClipId,
						$item['url'],						// $url,
						$title,								// $title,
						$description,						// $description,
						$item['thumbnail_url'],				// $thumbnail,
						strtotime($item['created_at'])		// $time
					);
					// TODO:
					// $video['view_count'];
					// $video['type'];
					// $video['language'];
					// $video['duration'];
				}
			}
		}
	}

	$clips = twitch_getClipsForUser($streamer['twitchId']);
	if ($clips) {
		$items = $clips['data'];
		for($j = 0; $j < count($items); $j++) {
			$item = $items[$j];

			$published_at  = strtotime($item['created_at']);
			if ($published_at > $lastUploadedOn) {
				$lastUploadedOn = $published_at;
			}

			foreach ($all_games as $game) {
				//if (strpos(strtolower($item['title']), strtolower($game['name'])) !== FALSE ||
				//		util_containsKeywords($item['title'], $game['keywords']))
				$title = remove_emoji_from_string($item['title']);
				$description = "";

				if (util_is_game_coverage_match($game, $title, $description)) {
					tryAddTwitchCoverageUnsure(
						$game,								// $game,
						$streamer,							// $dbchannel,
						$item['broadcaster_id'],			// $twitchChannelId,
						$item['broadcaster_name'],			// $twitchChannelName,
						null,  			// video id			// $twitchVideoId,
						$item['id'], 	// clip id			// $twitchClipId,
						$item['url'],						// $url,
						$title,								// $title,
						$description,						// $description,
						$item['thumbnail_url'],				// $thumbnail,
						strtotime($item['created_at'])		// $time
					);
					// TODO:
					// $clip['view_count'];
					// $clip['language'];
				}
			}
		}
	}
	//print_r($clips);
	$subs = twitch_countSubscribers($streamer['twitchId']);

	$views = $streamer['views'];
	$viewsData = twitch_getUsers($streamer['twitchId']);
	if ($viewsData) {
		if (count($viewsData['data']) > 0) {
			$views = $viewsData['data'][0]['view_count'];
		}
	}

	$db->exec("UPDATE twitchchannel SET lastscrapedon = " . time() . ", lastpostedon_updatedon = " . time() . ", subscribers = '" . $subs . "', views = '" . $views . "', lastpostedon = " . $lastUploadedOn . " WHERE id = " . $streamer['id'] . " ;");
	sleep(1);
}
//print_r($ids);

//print_r( twitch_getVideosForUsers($ids) );

$db->exec("UPDATE status SET `value` = " . time() . " WHERE `key` = 'cron_complete_refresh_coverage_twitch' ;");

echo "<b>Done!</b>\n";

?>
