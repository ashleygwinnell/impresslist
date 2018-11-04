<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Games
$doTimelimit = true;
$timeLimitStr = ($doTimelimit) ? ("AND twitchLastScraped < " . (time()-3600)):"";
$games = $db->query("SELECT * FROM game WHERE twitchId != 0 {$timeLimitStr} ORDER BY twitchLastScraped ASC;");
$num_games = count($games);

$streamers = $db->query("SELECT * FROM twitchchannel WHERE twitchId != 0 AND lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY lastscrapedon ASC;");
$num_streamers = count($streamers);

function tryAddTwitchCoverage($myChannelId, $twitchChannelId, $twitchChannelName, $twitchVideoId, $twitchClipId, $gameId, $url, $title, $description, $thumbnail, $time) {
	global $db;
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	echo "Found Coverage!<br/>\n";
	$stmt = $db->prepare("SELECT * FROM twitchchannel_coverage WHERE url = :url; ");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$existingCoverage = $stmt->query();
	if (count($existingCoverage) == 0) {

		echo "Adding coverage from {$twitchChannelName}<br/>\n";
		echo $title . "<br/>\n";
		echo $description . "<br/>\n";
		echo $url . "<br/>\n";
		echo $time . "<br/>\n";
		echo $thumbnail . "<br/>\n";
		echo "<hr/>\n";

		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO twitchchannel_coverage (`id`, `twitchchannel`, `twitchVideoId`, `twitchClipId`, `twitchChannelId`, `game`, `url`, `title`, `description`, `thumbnail`, `utime`, `thanked`, `removed`)
														VALUES (NULL, :twitchchannel, :twitchVideoId, :twitchClipId, :twitchChannelId, :game, :url, :title, :description, :thumbnail, :utime, 0, 0 ); ");
		$stmt->bindValue(":twitchchannel", $myChannelId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchVideoId", $twitchVideoId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchClipId", $twitchClipId, Database::VARTYPE_STRING);
		$stmt->bindValue(":twitchChannelId", $twitchChannelId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":description", $description, Database::VARTYPE_STRING);
		$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$e = $stmt->execute();


		@email_new_coverage($twitchChannelName, $url, $time);
		@slack_coverageAlert($twitchChannelName, $title, $url);

	} else {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database.</i><br/>\n";
	}
}



function tryAddTwitchChannel($channelOrUserId) {
	global $db;
	echo $channelOrUserId;
	$channels = $db->query("SELECT * FROM twitchchannel WHERE twitchId = {$channelOrUserId} LIMIT 1");
	if (count($channels) == 0) {
		// Add Channel.
		$users = twitch_getUsers($channelOrUserId);
		if ($users['data'] && count($users['data']) == 1) {
			$user = $users['data'][0];

			$success = db_try_add_twitch_channel_from_user_result( $user );
			if ($success) {
				$id = $db->lastInsertRowID();
				$channels = $db->query("SELECT * FROM twitchchannel WHERE id = {$id} LIMIT 1");
			}

		}
	}
	return $channels[0];
}

// Search by Game
// For each game
echo "<b>Games</b><br/>\n";
for($i = 0; $i < $num_games; ++$i)
{
	echo "<b>" . $games[$i]['name'] . "</b>!<br/>\n";

	$twitchId = $games[$i]['twitchId'];
	if (strlen($twitchId) > 0)
	{
		$videos = twitch_getVideosOfGame($twitchId);
		echo "<b>Videos:</b><br/>";

		if ($videos['data'] != null) {
			for($j = 0; $j < count($videos['data']); $j++) {
				$video = $videos['data'][$j];

				$channel = tryAddTwitchChannel($video['user_id']);
				tryAddTwitchCoverage($channel['id'], $video['user_id'], $video['user_name'], $video['id'], null, $games[$i]['id'], $video['url'], $video['title'], $video['description'], $video['thumbnail_url'], strtotime($video['created_at']));
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

				$channel = tryAddTwitchChannel($clip['broadcaster_id']);
				tryAddTwitchCoverage($channel['id'], $clip['broadcaster_id'], $clip['broadcaster_name'], null, $clip['id'], $games[$i]['id'], $clip['url'], $clip['title'], "", $clip['thumbnail_url'], strtotime($clip['created_at']));

				// TODO:
				// $clip['view_count'];
				// $clip['language'];
			}
		}

		$db->exec("UPDATE game SET twitchLastScraped = " . time() . " WHERE id = " . $games[$i]['id'] . " ;");
		sleep(2);
	}
	echo "<br/><br/>";
}

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

			foreach ($games as $game) {
				if (strpos(strtolower($item['title']), strtolower($game['name'])) !== FALSE ||
						strpos(strtolower($item['description']), strtolower($game['name'])) !== FALSE ||
						util_containsKeywords($item['title'], $game['keywords']) ||
						util_containsKeywords($item['description'], $game['keywords']))
					{
					tryAddTwitchCoverage($streamer['id'], $item['user_id'], $item['user_name'], $item['id'], null, $game['id'], $item['url'], $item['title'], $item['description'], $item['thumbnail_url'], strtotime($item['created_at']));
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

			foreach ($games as $game) {
				if (strpos(strtolower($item['title']), strtolower($game['name'])) !== FALSE ||
						util_containsKeywords($item['title'], $game['keywords']))
					{
					tryAddTwitchCoverage($streamer['id'], $item['broadcaster_id'], $item['broadcaster_name'], null, $item['id'], $game['id'], $item['url'], $item['title'], "", $item['thumbnail_url'], strtotime($item['created_at']));
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
	sleep(10);
}
//print_r($ids);

//print_r( twitch_getVideosForUsers($ids) );


echo "<b>Done!</b>\n";

?>
