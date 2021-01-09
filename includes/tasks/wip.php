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


//util_fixkeywords("toast time , smash up");

$publications = $db->query("SELECT * FROM publication WHERE id = 14 AND removed = 0 LIMIT 1;");
$rps = $publications[0];


// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);


?>
