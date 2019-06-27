<?php

set_time_limit(0);

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Twitters
$results = $db->query("SELECT * FROM oauth_twitteracc WHERE removed = 0 ORDER BY id ASC;");
usort($results, "sortById");

for($i = 0; $i < count($results); $i++) {
	echo "Updating: " . $results[$i]['twitter_handle'] . "... ";
	$r = twitter_getUserInfoByUsername($results[$i]['twitter_handle']);

	$stmt = $db->prepare("UPDATE oauth_twitteracc SET `twitter_name` = :name, `twitter_image` = :img WHERE `id` = :id; ");
	$stmt->bindValue(":name", 	decodeEmoticons($r->name), 	Database::VARTYPE_STRING);
	$stmt->bindValue(":img", 	$r->profile_image_url, 		Database::VARTYPE_STRING);
	$stmt->bindValue(":id",  	$results[$i]['id'], 		Database::VARTYPE_INTEGER);
	$done = $stmt->execute();

	twitter_util_scrape_relationships($results[$i]['id'], $results[$i]['twitter_handle']);
	echo "Done!<br/>";
}

// Facebooks
$results = $db->query("SELECT * FROM oauth_facebookacc WHERE removed = 0 ORDER BY id ASC;");
usort($results, "sortById");
// http://graph.facebook.com/" . $user['id'] . "/picture?type=square



?>
