<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
include_once("init.php");

// Users
$users = $db->query("SELECT id, forename, surname, email, color FROM user;");
$num_users = count($users);

// Games
$games = $db->query("SELECT * FROM game;");
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