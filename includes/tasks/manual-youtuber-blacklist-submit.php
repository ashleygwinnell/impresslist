<?php

ini_set("allow_url_fopen", "On");
error_reporting(E_ALL);

$startTime = time();
$require_login = true;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);

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
			$success = youtuber_blacklist_by_video_id($videoId, $errorMessage);
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
		$errorMessage = "Invalid youtube video url";
		echo "error: " . $errorMessage . "<br/>";
	}
}



?>
