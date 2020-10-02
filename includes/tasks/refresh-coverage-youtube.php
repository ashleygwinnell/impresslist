<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

// Temp test
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 165;");
//$db->exec("UPDATE publication SET lastscrapedon = 0;");

// Youtubers
$youtubers = $db->query("SELECT * FROM youtuber WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY RAND() ASC LIMIT 50;");
$num_youtubers = count($youtubers);

// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);

//print_r($games);
//print_r($watchedgames);

// for each youtuber
for($i = 0; $i < $num_youtubers; ++$i)
{
	echo "<b>" . $youtubers[$i]['name'] . "</b>!<br/>\n";

	$youtubeChannel = $youtubers[$i]['channel'];
	if (strlen($youtubeChannel) > 0)
	{
		if (strlen($youtubers[$i]['youtubeId']) == 0 || strlen($youtubers[$i]['youtubeUploadsPlaylistId']) == 0) {
			// better update this 'un.
			$details = youtube_v3_getInformation($youtubeChannel);
			//print_r($details);
			$youtubers[$i]['youtubeId'] = $details['id'];
			$youtubers[$i]['youtubeUploadsPlaylistId'] = $details['playlists']['uploads'];

			$stmt = $db->prepare("UPDATE youtuber SET youtubeId = :youtubeId, youtubeUploadsPlaylistId = :youtubeUploadsPlaylistId WHERE id = :id; ");
			$stmt->bindValue(":youtubeId", $details['id'], Database::VARTYPE_STRING);
			$stmt->bindValue(":youtubeUploadsPlaylistId", $details['playlists']['uploads'], Database::VARTYPE_STRING);
			$stmt->bindValue(":id", $youtubers[$i]['id'], Database::VARTYPE_INTEGER);
			$stmt->execute();
			sleep(1);
		}

		$uploads = youtube_v3_getUploads($youtubers[$i]['youtubeUploadsPlaylistId']);
		if ($uploads != 0)
		{

			foreach($uploads as $video) {
				$videoTitle = remove_emoji_from_string($video['title']);
				$videoDescription = remove_emoji_from_string($video['description']);
				//$link = "https://www.youtube.com/watch?v=" . $video['id'];
				$videoThumbnail = $video['thumbnail'];
				$videoTime = strtotime($video['publishedOn']);
				echo $title . "<br/>";

				foreach ($games as $game) {
					if (util_is_game_coverage_match($game, $title, $description))
					{
						coverage_tryAddYoutubeCoverageUnsure(
							$game,
							null,
							$youtubers[$i]['id'],
							$youtubers[$i]['youtubeId'],
							$youtubers[$i]['name'],
							$video['id'],
							$videoTitle,
							$videoDescription,  // this is new
							$videoThumbnail,
							$videoTime,
							true
						);
					}
				}

				foreach ($watchedgames as $watchedgame) {
					if (util_is_game_coverage_match($watchedgame, $videoTitle, $videoDescription)) {
						echo "<h4>Found Coverage!</h4>";

						coverage_tryAddYoutubeCoverageUnsure(
							null,
							$watchedgame,
							$youtubers[$i]['id'],
							$youtubers[$i]['youtubeId'],
							$youtubers[$i]['name'],
							$video['id'],
							$videoTitle,
							$videoDescription,  // this is new
							$videoThumbnail,
							$videoTime,
							true
						);
					}
				}
			}
		}
		$db->exec("UPDATE youtuber SET lastscrapedon = " . time() . " WHERE id = " . $youtubers[$i]['id'] . " ;");
		sleep(1);
		//die();

	}


}
$db->exec("UPDATE status SET `value` = " . time() . " WHERE `key` = 'cron_complete_refresh_coverage_youtube' ;");

echo "<b>Done!</b>\n";

?>
