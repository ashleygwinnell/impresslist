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

// print_r($rps);

// coverage_scrapePublicationRSS($games, $watchedgames, $rps, false);
//coverage_scrapePublicationHomepage($games, $watchedgames, $rps, false);
// $url = "https://www.rockpapershotgun.com/2020/10/01/indie-collective-10mg-announce-their-first-dose-of-bite-sized-games/";
// coverage_scrapePublicationUrl($games, $watchedgames, $rps, $url, false);
// coverage_scrapePublicationUrl($games, $watchedgames, $rps, $url, true, false);
// https://discordapp.com/api/webhooks/594889377046593536/rGHB4w_7IBXEg1FkRlZxpZ6IZAHI5FXfjv1-fV9pQOpUblYE0A2tEu19wJsDBGO-HaGZ
// $fromName = "Finger Guns";
// $coverageTitle = "EGX 2019: The Complete Recap &ndash; Day One | Day Two | Day Three";
// $url = "http://fingerguns.net/features/2019/10/21/egx-2019-the-full-recap-day-one-day-two-day-three/";
// $data = array(
// 	"content" => "**{$fromName}** - {$coverageTitle}: \n{$url}"
// );
// discord_webhook("594889377046593536", "rGHB4w_7IBXEg1FkRlZxpZ6IZAHI5FXfjv1-fV9pQOpUblYE0A2tEu19wJsDBGO-HaGZ", $data, true);

// $articlecontents_lc = "asdahsasdasdtoast timeaasdasd";
// echo $articlecontents_lc . "<br/>";

// $name = "Toast Time: Smash Up!";
// $keywords = "toast time,smash up";

// $r = util_muddyCoverageContains($articlecontents_lc, $name, $keywords);
// echo (($r)?"true":"false") . "<br/>\n";

// echo "<b>Done!</b>\n";


?>
