<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
$require_config = true;
include_once("includes/checks.php");
include_once("init.php");


// Users
$users = $db->query("SELECT user.id, forename, surname, email, color, lastactivity, count(email.id) as num_emails, admin FROM user LEFT JOIN email on email.user_id = user.id WHERE user.removed = 0 group by user.id;");
$num_users = count($users);

// Games
$games = $cache->get("games");
if ($games == NULL) {
	$games = $db->query("SELECT * FROM game;");
	$cache->set("games", $games, 3600);
}
$num_games = count($games);


// Settings
$settings = array();
$settings_resultset = $db->query("SELECT * FROM settings;");
foreach ($settings_resultset as $row) { $settings[$row['key']] = $row['value']; }
$num_settings = count($settings);
//print_r($settings);


//$youtube = youtube_getInformation("asdasdsauhsdfkf2398423");
//print_r($youtube);

include_once("includes/page.html");


$db->close();



?>
