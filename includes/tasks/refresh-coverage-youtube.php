<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Temp test
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 165;");
//$db->exec("UPDATE publication SET lastscrapedon = 0;");

// Youtubers
$youtubers = $db->query("SELECT * FROM youtuber WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY lastscrapedon ASC;");
$num_youtubers = count($youtubers);

// Games
$games = $db->query("SELECT * FROM game;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame;");
$num_watchedgames = count($watchedgames);


function tryAddYoutubeCoverage($youtuberId, $youtuberName, $gameId, $watchedGameId, $title, $url, $thumbnail, $time) {
	global $db;
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	echo "Found Coverage!<br/>\n";
	$stmt = $db->prepare("SELECT * FROM youtuber_coverage WHERE url = :url; ");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$existingCoverage = $stmt->query();
	if (count($existingCoverage) == 0) {

		echo "Adding coverage from {$youtuberName}<br/>\n";
		echo $title . "<br/>\n";
		echo $url . "<br/>\n";
		echo $time . "<br/>\n";
		echo $thumbnail . "<br/>\n";
		echo "<hr/>\n";
		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO youtuber_coverage (id, youtuber, person, game, watchedgame, url, title, thumbnail, `utime`)
														VALUES (NULL, :youtuber, NULL, :game, :watchedgame, :url, :title, :thumbnail, :utime ); ");
		$stmt->bindValue(":youtuber", $youtuberId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$stmt->execute();

		@email_new_youtube_coverage($youtuberName, $url, $time);
		@slack_coverageAlert($youtuberName, $title, $url);

	} else {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database.</i><br/>\n";
	}
}



// for each publication
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
					if (strpos($title, $game['name']) !== FALSE ||
						strpos($description, $game['name']) !== FALSE)
					{
						tryAddYoutubeCoverage(
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
					if (strpos($title, $watchedgame['name']) !== FALSE ||
						strpos($description, $watchedgame['name']) !== FALSE)
					{
						tryAddYoutubeCoverage(
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
