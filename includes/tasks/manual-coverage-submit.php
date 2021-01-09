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
if (strlen(trim($url)) == 0) {
	echo "invalid url";
	die();
}

$endbaseUrl = strpos($url, "/", 8);

if ($endbaseUrl !== FALSE) {
	$baseUrl = substr($url, 0, $endbaseUrl+1);

	if ($baseUrl === "https://www.youtube.com/") {
		$ytbaseurl = "https://www.youtube.com/watch?v=";
		if (strpos($url, $ytbaseurl) !== FALSE) {
			$videoId = substr($url, 32);

			$autoSubmitted = false;
			$errorMessage = "";
			$success = youtuber_coverage_manual_submit($videoId, $errorMessage, $autoSubmitted);
			if ($success) {
				echo "submitted successfully <br/>";
				echo (($autoSubmitted)?"auto":"") . "<br/>";
			}
			else {
				echo "error: " . $errorMessage . "<br/>";
			}
		}
		else {
			die("Could not find youtube video in url: " . $baseUrl);
		}
	}
	else {
		$errorMessage = "";
		$success = publication_coverage_manual_submit($url, $errorMessage);
		if ($success) {
			echo "submitted successfully <br/>";
		}
		else {
			echo "error: " . $errorMessage . "<br/>";
		}

	}
}



?>