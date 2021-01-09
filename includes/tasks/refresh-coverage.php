<?php

ini_set("allow_url_fopen", "On");
set_time_limit(0);
error_reporting(E_ALL ^ E_NOTICE);

$testMode = false;

$max_publications = 50;
$max_url_scrapes_per_publication = 50;
if ($testMode) {
	$max_publications = 1;
	$max_url_scrapes_per_publication = 50;
}


$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/checks.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

error_reporting(E_ALL ^ E_NOTICE);
//die($_SERVER['DOCUMENT_ROOT']);

// Temp test
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 228;");//nintendo times
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 158;");//onelifeleft
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 29;"); // appadvice
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 201;"); // gonintendo
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 207;"); // nintendo everything
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 89;");//game people - check pub date
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 202;");//nintendoworldreport
// $db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 200;");//nintendonl
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 149;");//tech raptor
//$db->exec("UPDATE publication SET lastscrapedon = 0;");

util_publication_url_hash_purgeold();

// error_reporting(E_ALL ^ E_NOTICE);
// use Readability\Readability;
// $readabilitytest = new Readability("", "");
// die("no");

//$db->exec("DELETE FROM publication_coverage WHERE utime > " . (time()-(86400*3)) . ";");
// $db->exec("DELETE FROM cache_external_urlbools WHERE 1 = 1;");
// $db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 66;");
//die();

// Publications
$publications = $db->query("SELECT * FROM publication WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY RAND() ASC;");
$num_publications = count($publications);

// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);


echo "<b>Refresh Coverage:</b><br/>\n";
echo "<i>Checking " . $num_publications . " publications (".$max_publications." max)...</i><br/>\n";
// for each publication
for($i = 0; $i < $num_publications && $i < $max_publications; $i++) {

	echo "<b>" . $publications[$i]['name'] . "</b>!<br/>\n";

	// Scrape RSS
	$rssError = "";
	$pageError = "";
	$rssScrapeSuccess = coverage_scrapePublicationRSS($games, $watchedgames, $publications[$i], true, $rssError);

	// Scrape homepage
	if (!$rssScrapeSuccess)
	{
		coverage_scrapePublicationHomepage($games, $watchedgames, $publications[$i], true, $pageError);
	}
	echo "<hr/>\n";

	$scrapeStatus = json_encode(array(
		"rss" => $rssError,
		"home" => $pageError
	));

	// Update database lastscrapedon value.
	$stmt = $db->prepare("UPDATE publication SET lastscrapedon = :lastscrapedon, lastscrapestatus = :lastscrapestatus WHERE id = :id ; ");
	$stmt->bindValue(":lastscrapedon", 		time(), 					Database::VARTYPE_INTEGER);
	$stmt->bindValue(":lastscrapestatus", 	$scrapeStatus, 				Database::VARTYPE_STRING);
	$stmt->bindValue(":id", 				$publications[$i]['id'], 	Database::VARTYPE_INTEGER);
	$stmt->execute();

	//sleep(1);
}
$db->exec("UPDATE status SET `value` = " . time() . " WHERE `key` = 'cron_complete_refresh_coverage' ;");

echo "<b>Done!</b>\n";

?>
