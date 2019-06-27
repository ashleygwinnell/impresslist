<?php

set_time_limit(0);
header("Content-Type: text/html; charset=utf-8");

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// only scrape new info every 7 days.
$scrapetime = 86400 * 7;

$forAccount = "forcehabit";
$result = $db->query("SELECT * FROM oauth_twitteracc WHERE twitter_handle = '" . $forAccount . "' AND removed = 0;");
if (count($result) != 1) {
	die('invalid account');
}

$username = $result[0]['twitter_name'];

$prePreCache = $db->query("SELECT * FROM cache_external_twitteracc WHERE associated_oauth_twitteracc = '" . $result[0]['id'] . "';");

$preCache = array();
foreach($prePreCache as $item) {
	$preCache[$item['twitter_id']] = $item;
}

$ids = twitter_listFollowingIds($username);
if (count($ids) == 0) {
	echo "Rate limit exceeded.";
	die();
}

$toProcessList = array();
$toProcessDict = array();

for($index = 0; $index < count($ids); $index++) {
	$id = $ids[$index];

	$newCache = true;
	$updateCache = false;

	if (!!$preCache[$id]) { // we have recent cache for this one.
		$newCache = false;
		$lastscrapedon = $preCache[$id]['lastscrapedon'];
		if ($lastscrapedon < time() - $scrapetime) {
			$updateCache = true;
		}
	}

	if ($newCache || $updateCache) {

		$toProcessList[] = array("id" => $id, "new" => $newCache, "update" => $updateCache);
		$toProcessDict[$id] = array("new" => $newCache, "update" => $updateCache);
		if (count($toProcessList) >= 100) {
			break;
		}
	}
}

if (count($toProcessList) > 0) {
	$processIds = [];
	for($i = 0; $i < count($toProcessList); $i++) {
		$processIds[] = $toProcessList[$i]['id'];
	}

	$infos = twitter_getUserInfos($processIds);

	for($i = 0; $i < count($infos); $i++) {
		$info = $infos[$i];
		$id = $info->id_str;
		$utc_date = $info->status->created_at;

		$datetime = new DateTime($utc_date);
		$datetime->setTimezone(new DateTimeZone('Europe/Zurich'));
		$timestamp = $datetime->format('U');
		$now = time();

		$name = decodeEmoticons($info->name);
		$bio = decodeEmoticons($info->description);
		if ($toProcessDict[$info->id_str]['new']) {
			echo "Adding cache for " . $name . " (@" . $info->screen_name . ")...";
			$stmt = $db->prepare("INSERT INTO cache_external_twitteracc (id, twitter_id, twitter_name, twitter_handle, twitter_bio, twitter_image, twitter_lastpostedon, associated_oauth_twitteracc, lastscrapedon)
																 VALUES (NULL, :twitter_id, :twitter_name, :twitter_handle, :twitter_bio, :twitter_image, :twitter_lastpostedon, :oauthid, :curtime);");
			$stmt->bindValue(":twitter_id", 			$info->id_str, 				Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_name", 			$name, 						Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_handle", 		$info->screen_name, 		Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_bio", 			$bio, 						Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_image", 			$info->profile_image_url, 	Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_lastpostedon", 	$timestamp, 				Database::VARTYPE_INTEGER);
			$stmt->bindValue(":oauthid", 				$result[0]['id'], 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":curtime", 				$now, 						Database::VARTYPE_INTEGER);
			$done = $stmt->execute();
		}
		else if ($toProcessDict[$info->id_str]['update']) {
			echo "Updating cache for " . $name . " (@" . $info->screen_name . ")...";
			//echo $bio . "<br/>";
			//print_r($info);
			$stmt = $db->prepare("UPDATE cache_external_twitteracc SET
									twitter_name = :twitter_name,
									twitter_handle = :twitter_handle,
									twitter_bio = :twitter_bio,
									twitter_image = :twitter_image,
									twitter_lastpostedon = :twitter_lastpostedon,
									lastscrapedon = :curtime
								 WHERE twitter_id = :twitter_id
								 LIMIT 1 ;");
			$stmt->bindValue(":twitter_id", 			$id, 						Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_name", 			$name, 						Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_handle", 		$info->screen_name, 		Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_bio", 			$bio, 						Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_image", 			$info->profile_image_url, 	Database::VARTYPE_STRING);
			$stmt->bindValue(":twitter_lastpostedon", 	$timestamp, 				Database::VARTYPE_INTEGER);
			$stmt->bindValue(":oauthid", 				$result[0]['id'], 			Database::VARTYPE_INTEGER);
			$stmt->bindValue(":curtime", 				$now, 						Database::VARTYPE_INTEGER);
			$done = $stmt->execute();
		}
		$total++;

		echo "Done!<br/>";
	}
}
echo "<b>Total:</b>&nbsp;" . $total . "<br/>";
echo "<b>Completely done!</b>"


?>
