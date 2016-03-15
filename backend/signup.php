<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
include_once("init.php");

// Games
$games = $cache->get("games");
if ($games == NULL) {
	$games = $db->query("SELECT * FROM game;");
	$cache->set("games", $games, 3600);
}
$num_games = count($games);

include_once("includes/signup.html");

$db->close();

?>