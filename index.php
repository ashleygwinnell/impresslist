<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
include_once("init.php");

// Sorts
function sortByName($a, $b) { return $a['name'] > $b['name']; }

// People
$people = array();
$people_resultset = $db->query("SELECT * FROM person WHERE removed = 0;");
while($row = $people_resultset->fetchArray(SQLITE3_ASSOC)) { $people[] = $row; }
$num_people = count($people);
usort($people, "sortByName");

// Publications
$publications = array();
$publications_resultset = $db->query("SELECT * FROM publication WHERE removed = 0;");
while($row = $publications_resultset->fetchArray(SQLITE3_ASSOC)) { $publications[] = $row; }
$num_publications = count($publications);
usort($publications, "sortByName");

// Add Publications to People
$personPublications = array();
$personPublications_resultset = $db->query("SELECT * FROM person_publication;");
while($row = $personPublications_resultset->fetchArray(SQLITE3_ASSOC)) { $personPublications[] = $row; }
$num_personPublications = count($personPublications);

// Youtubers
$youtubers = array();
$youtubers_resultset = $db->query("SELECT * FROM youtuber WHERE removed = 0;");
while($row = $youtubers_resultset->fetchArray(SQLITE3_ASSOC)) { $youtubers[] = $row; }
$num_youtubers = count($youtubers);
usort($youtubers, "sortByName");


// Emails
$emails = array();
$emails_resultset = $db->query("SELECT * FROM email WHERE unmatchedrecipient == 0 ORDER BY utime DESC;");
while($row = $emails_resultset->fetchArray(SQLITE3_ASSOC)) { $emails[] = $row; }
$num_emails = count($emails);

// Users
$users = array();
$users_resultset = $db->query("SELECT id, forename, surname, email, color FROM user;");
while($row = $users_resultset->fetchArray(SQLITE3_ASSOC)) { $users[] = $row; }
$num_users = count($users);

// Games
$games = array();
$games_resultset = $db->query("SELECT * FROM game;");
while($row = $games_resultset->fetchArray(SQLITE3_ASSOC)) { $games[] = $row; }
$num_games = count($games);

// Settings
$settings = array();
$settings_resultset = $db->query("SELECT * FROM settings;");
while($row = $settings_resultset->fetchArray(SQLITE3_ASSOC)) { $settings[$row['key']] = $row['value']; }
$num_settings = count($settings);
//print_r($settings);


//$youtube = youtube_getInformation("asdasdsauhsdfkf2398423");
//print_r($youtube);

include_once("includes/page.html");


$db->close();



?>