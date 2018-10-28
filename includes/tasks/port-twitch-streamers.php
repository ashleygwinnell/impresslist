<?php

set_time_limit(0);

//
// Refresh Youtuber subs/views count.php
//
$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

function findTwitchName($strings = array()) {
	for($i = 0; $i < count($strings); $i++) {
		$str  = strtolower($strings[$i]);

		$start = strpos($str, "twitch.tv/");
		if ($start < 0 || $start === FALSE) { continue; }
		$start += 10;
		$str = substr($str, $start);

		$end = 999999999;
		$endCharacters = [" ", "
", ")", ".", "/", "!", "\n", "\t", "\r"];
		for($j = 0; $j < count($endCharacters); $j++) {
			$thisEnd = @strpos($str, $endCharacters[$j]);
			if ($thisEnd !== FALSE) {
				if ($thisEnd <= $end) {
					$end = $thisEnd;
				}
				//echo $end;
			}
		}
		if ($end == 999999999) {
			return substr($str, 0);
		}

		//echo 0 . "-".$end . " : ";
		return substr($str, 0, $end);
	}
	return "";
}

function try_add_from_twitch_name($twitchName) {
	if (strlen($twitchName) > 0) {

		$users = twitch_getUsersFromLogin($twitchName);
		if ($users['data'] && count($users['data']) == 1) {
			$user = $users['data'][0];
			//echo $user['id'] . " - " . $user['login']. "<br/>";
			$success = db_try_add_twitch_channel_from_user_result($user);
			return $success;

			//print_r(twitch_getUsersFromLogin($twitchName));
			//break;
		}
	}
	return false;
}


echo "Port Into Twitch Streamers<Br/>";
echo "<br/>";



echo "<B>People</b><Br/>";
$people = $db->query("SELECT * from person WHERE notes LIKE '%twitch.tv/%' ");
//print_r($people);
for($i = 0; $i < count($people); $i++) {
	$twitchName = findTwitchName([$people[$i]['notes']]);
	echo $people[$i]['firstname']  . " " . $people[$i]['surnames'] . " = " . $twitchName . "<br/>";

	$success = try_add_from_twitch_name($twitchName);
	if ($success) {
		$id = $db->lastInsertRowID();

		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO person_twitchchannel (`id`, `person`, `twitchchannel`)
														VALUES (NULL, :person, :twitchchannel); ");
		$stmt->bindValue(":person", $people[$i]['id'], Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchchannel", $id, Database::VARTYPE_INTEGER);
		$e = $stmt->execute();
		echo "Added" . "<br/>";

		$db->query("UPDATE twitchchannel SET
						twitter = '" . $people[$i]['twitter'] . "',
						twitter_followers = '" . $people[$i]['twitter_followers'] . "',
						twitter_updatedon = '" . $people[$i]['twitter_updatedon'] . "'
						email = '" . $people[$i]['email'] . "'
						WHERE id = '" . $id . "';");

		sleep(1);
	}
}
echo "<br/>";



echo "<b>YouTubers</b><Br/>";
$youtubers = $db->query("SELECT * from youtuber WHERE notes LIKE '%twitch.tv/%' OR description LIKE '%twitch.tv/%' ");
//print_r($youtubers);
for($i = 0; $i < count($youtubers); $i++) {
	$twitchName = findTwitchName([$youtubers[$i]['notes'], $youtubers[$i]['description']]);

	// if (strpos($youtubers[$i]['description'], 'twitch.tv/') >= 0) {
	// 	echo $youtubers[$i]['description'] . "<br/>";
	// }
	// if (strpos($youtubers[$i]['notes'], 'twitch.tv/') >= 0) {
	// 	echo $youtubers[$i]['notes'] . "<br/>";
	// }

	echo $youtubers[$i]['name']  . " " . $people[$i]['name_override'] . " = " . $twitchName . "<br/>";

	$success = try_add_from_twitch_name($twitchName);
	if ($success){
		echo "Added!<Br/>";
	}
	sleep(1);
}
echo "<br/>";



echo "<b>Publications</b><Br/>";
$publications = $db->query("SELECT * from publication WHERE notes LIKE '%twitch.tv/%';");
//print_r($youtubers);
for($i = 0; $i < count($publications); $i++) {
	$twitchName = findTwitchName([$publications[$i]['notes']]);
	echo $publications[$i]['name']  . " = " . $twitchName . "<br/>";

	$success = try_add_from_twitch_name($twitchName);
	if ($success){
		echo "Added!<Br/>";
	}
	sleep(1);
}
echo "<br/>";

die();
?>
