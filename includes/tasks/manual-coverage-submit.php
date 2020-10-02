<?php

ini_set("allow_url_fopen", "On");
error_reporting(E_ALL);

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);

$url = $_GET['url'];
echo "Submitting url: " . $url . "<br/>\n";

$endbaseUrl = strpos($url, "/", 8);

if ($endbaseUrl !== FALSE) {
	$baseUrl = substr($url, 0, $endbaseUrl+1);

	$stmt = $db->prepare("SELECT * FROM publication WHERE url LIKE :url AND removed = 0 LIMIT 1; ");
	$stmt->bindValue(":url", $baseUrl . "%", Database::VARTYPE_STRING);
	$publications = $stmt->query();
	// print_r($publications);
	if (count($publications) != 1) {
		die("Could not find publication with base url: " . $baseUrl);
		// TODO: check without trailing slash, and with http://
	}
	else {
		$publication = $publications[0];
		coverage_scrapePublicationUrl($games, $watchedgames, $publication, $url, false, true);
		echo "Done!";
	}
}



?>
