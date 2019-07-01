<?php

//
// A mix between Highrise, Promoter and distribute().
//
$require_login = true;
$require_config = true;
include_once("includes/checks.php");
include_once("init.php");

// echo "person <br/>";
// print_r( db_keysassignedtotype($db, $user['currentGame'], 'switch', 'eu', 'person', 333) );

// echo "publication <br/>";
// print_r( db_keysassignedtotype($db, $user['currentGame'], 'switch', 'eu', 'publication', 194) );

// echo "youtuber <br/>";
// print_r( db_keysassignedtotype($db, $user['currentGame'], 'switch', 'eu', 'youtuber', 244) );

//die();

// Users
$stmt = $db->prepare("SELECT user.id, company, forename, surname, email, color, lastactivity, count(email.id) as num_emails, admin, superadmin FROM user LEFT JOIN email on email.user_id = user.id WHERE user.removed = 0 AND user.company = :company group by user.id;");
$stmt->bindValue(":company", $user_company, Database::VARTYPE_INTEGER);
$users = $stmt->query();
$num_users = count($users);

// Games
$games = $cache->get("games");
if ($games == NULL) {
	$stmt = $db->prepare("SELECT * FROM game WHERE company = :company AND removed = 0;");
	$stmt->bindValue(":company", $user_company, Database::VARTYPE_INTEGER);
	$games = $stmt->query();
	$cache->set("games", $games, 3600);
}
$num_games = count($games);

// Games
$audiences = $cache->get("audiences");
if ($audiences == NULL) {
	$stmt = $db->prepare("SELECT * FROM audience WHERE company = :company;");
	$stmt->bindValue(":company", $user_company, Database::VARTYPE_INTEGER);
	$audiences = $stmt->query();
	$cache->set("audiences", $audiences, 3600);
}
$num_audiences = count($audiences);


// Settings
$settings = array();
$stmt = $db->prepare("SELECT * FROM settings WHERE company = :company OR company IS NULL;");
$stmt->bindValue(":company", $user_company, Database::VARTYPE_INTEGER);
$settings_resultset = $stmt->query();
foreach ($settings_resultset as $row) { $settings[$row['key']] = $row['value']; }
$num_settings = count($settings);
//print_r($settings);


//$youtube = youtube_getInformation("asdasdsauhsdfkf2398423");
//print_r($youtube);

require_once("includes/page.html");


$db->close();



?>
