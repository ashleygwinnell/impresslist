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
$youtubers = $db->query("SELECT * FROM youtuber WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY lastscrapedon ASC;");
$num_youtubers = count($youtubers);

// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);

//print_r($games);
//print_r($watchedgames);

// tryAddYoutubeCoverage now in coverage.php

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
				$title = $video['title'];
				$link = "https://www.youtube.com/watch?v=" . $video['id'];
				$thumbnail = $video['thumbnail'];
				$published = strtotime($video['publishedOn']);
				echo $title . "<br/>";

				foreach ($games as $game) {
					if (util_is_game_coverage_match($game, $title, $description))
					{
						echo "<h4>Found Coverage!</h4>";
						tryAddYoutubeCoverage(
							$game['company'],
							$youtubers[$i]['id'],
							$youtubers[$i]['name'],
							$game['id'],
							0,
							$title,
							$link,
							$thumbnail,
							$published
						);
					}
				}

				foreach ($watchedgames as $watchedgame) {
					if (util_is_game_coverage_match($watchedgame, $title, $description)) {
						echo "<h4>Found Coverage!</h4>";
						tryAddYoutubeCoverage(
							0,
							$youtubers[$i]['id'],
							$youtubers[$i]['name'],
							0,
							$watchedgame['id'],
							$title,
							$link,
							$thumbnail,
							$published
						);
					}
				}
			}
		}
		$db->exec("UPDATE youtuber SET lastscrapedon = " . time() . " WHERE id = " . $youtubers[$i]['id'] . " ;");
		sleep(1);
		//die();

		/*$youtubeDetails = youtube_getUploads($youtubeChannel);
		if ($youtubeDetails != 0)
		{
			//print_r($youtubeDetails);
			foreach($youtubeDetails['feed']['entry'] as $video) {
				$link = $video['link']['0']['href'];
				$title = $video['title']['$t'];
				$description = $video['content']['$t'];
				$published = strtotime($video['published']['$t']);

				$link = str_replace("&feature=youtube_gdata", "", $link);

				$title = $video['media$group']['media$title']['$t'];
				$description = $video['media$group']['media$description']['$t'];
				$thumbnail = $video['media$group']['media$thumbnail'][0]['url'];

				echo $title . "<br/";

				foreach ($games as $game) {
					if (strpos($title, $game['name']) !== FALSE ||
						strpos($description, $game['name']) !== FALSE) {

						echo "<a href='{$link}'>{$title}</a><br/>";
						echo "<i>{$published}</i><br/>";
						echo "<img src='{$thumbnail}'/><br/>";
						//echo "{$description}<br/>";
						echo "<br/>";

						tryAddYoutubeCoverage(
							0
							$youtubers[$i]['id'],
							$youtubers[$i]['name'],
							$game['id'],
							0
							$title,
							$link,
							$published
						);

					}
				}


			}
		}*/
	}


}

echo "<b>Done!</b>\n";

?>
