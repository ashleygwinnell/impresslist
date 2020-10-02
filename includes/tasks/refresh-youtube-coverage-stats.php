<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

//$r = youtube_v3_getInformation("UCLR08NT874M_Mpgg9vtkv-g");

//$up = youtube_v3_getUploads($r['playlists']['uploads']);
//print_r($up);
//die();
//$v = youtube_v3_getVideoStatistics(array("8MYN6bg_up4", "MMhZGS0yZic"));
//$v = youtube_v3_getVideoStatistics("8MYN6bg_up4");
//print_r($v);

function scrape_youtube_stats() {
	global $db;
	$week = 86400 * 7; // weekly scrape
	$youtubeCoverage = $db->query("SELECT * FROM youtuber_coverage WHERE youtuber != 0 AND game != 0 AND removed = 0 AND lastscrapedon < " . (time() - $week) . " ORDER BY lastscrapedon ASC LIMIT 30;");
	$youtubeVideoIds = array();
	//$youtubeVideoIdsToUrls = array();
	$youtubeVideoIdsToInternalIds = array();
	for($i = 0; $i < count($youtubeCoverage); $i++) {
		$url = $youtubeCoverage[$i]['url'];
		$ytId = substr($url, strpos($url, "=")+1);
		$youtubeVideoIds[] = $ytId;
		//$youtubeVideoIdsToUrls[$ytId] = $url;
		$youtubeVideoIdsToInternalIds[$ytId] = $youtubeCoverage[$i]['id'];
	}
	//print_r($youtubeVideoIds);
	$totalScraped = 0;

	// TODO: Does this only do max 100 at a time?
	$stats = youtube_v3_getVideoStatistics($youtubeVideoIds);
	//print_r($stats);

	$keys = array_keys($stats);
	for($i = 0; $i < count($keys); $i++) {
		$ytId = $keys[$i];
		$statsThis = $stats[$ytId];
		$id = $youtubeVideoIdsToInternalIds[$ytId];

		$viewCount = (isset($statsThis['viewCount'])) ? $statsThis['viewCount'] : 0;
		$likeCount = (isset($statsThis['likeCount'])) ? $statsThis['likeCount'] : 0;
		$dislikeCount = (isset($statsThis['dislikeCount'])) ? $statsThis['dislikeCount'] : 0;
		$favoriteCount = (isset($statsThis['favoriteCount'])) ? $statsThis['favoriteCount'] : 0;
		$commentCount = (isset($statsThis['commentCount'])) ? $statsThis['commentCount'] : 0;

		$stmt = $db->prepare("UPDATE youtuber_coverage SET viewCount = :viewCount, likeCount = :likeCount, dislikeCount = :dislikeCount, favoriteCount = :favoriteCount, commentCount = :commentCount, lastscrapedon = :lastscrapedon WHERE id = :id ;");
		$stmt->bindValue(":viewCount", $viewCount, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":likeCount", $likeCount, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":dislikeCount", $dislikeCount, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":favoriteCount", $favoriteCount, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":commentCount", $commentCount, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":lastscrapedon", time(), Database::VARTYPE_INTEGER);
		$stmt->bindValue(":id", $id, Database::VARTYPE_STRING);
		$rs = $stmt->execute();

		if (!$rs) {
			echo "<b>Error scraping video " . $ytId . "</b><br/>\n";
			echo $stmt->error;
			print_r($rs);
		} else {
			echo "Scraped video " . $ytId . "<br/>\n";
			$totalScraped++;
		}
	}
	echo "<b>Scraped {$totalScraped} total!</b><br/>\n";
}
//update_youtube_stats_for_game(6);
scrape_youtube_stats();


echo "<b>Done!</b>\n";


?>
