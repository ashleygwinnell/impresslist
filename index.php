<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
include_once("init.php");

// Sorts
function sortByName($a, $b) { return $a['name'] > $b['name']; }

// People
$people = $db->query("SELECT * FROM person WHERE removed = 0;");
$num_people = count($people);
usort($people, "sortByName");

// Publications
$publications = $db->query("SELECT * FROM publication WHERE removed = 0;");
$num_publications = count($publications);
usort($publications, "sortByName");

// Add Publications to People
$personPublications = $db->query("SELECT * FROM person_publication;");
$num_personPublications = count($personPublications);

// Youtubers
$youtubers = $db->query("SELECT * FROM youtuber WHERE removed = 0;");
$num_youtubers = count($youtubers);
usort($youtubers, "sortByName");


// Emails
$emails = $db->query("SELECT * FROM email WHERE unmatchedrecipient = 0 ORDER BY utime DESC;");
$num_emails = count($emails);
for($i = 0; $i < $num_emails; $i++) { 
	$emails[$i]['contents'] = utf8_encode($emails[$i]['contents']);
}
//print_r($emails);

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