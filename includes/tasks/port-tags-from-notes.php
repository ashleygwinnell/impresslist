<?php

set_time_limit(0);

//
// Refresh Youtuber subs/views count.php
//
$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

echo "Port Tags from Notes<Br/>";
echo "<br/>";

function create_tags_array($name, $notes) {
	$name = strtolower($name);
	$notes = strtolower($notes);

	$tags = listtags();
	$keys = array_keys($tags);
	$results = [];
	for($i = 0; $i < count($keys); $i++) {
		//echo $keys[$i];
		if (!in_array($keys[$i], $results)) {
			// if (strpos($name, $keys[$i]) !== FALSE) {
			// 	$results[] = $keys[$i];
			// }
			// if (strpos($notes, $keys[$i])  !== FALSE) {
			// 	$results[] = $keys[$i];
			// }
			// else {
				$keywords = $tags[$keys[$i]]['autokeywords'];
				//print_r($keywords);
				for($j = 0; $j < count($keywords); $j++) {
					if (strpos($name, $keywords[$j]) !== FALSE ||
						strpos($notes, $keywords[$j]) !== FALSE
						) {
						$results[] = $keys[$i];
						echo $keywords[$j] . " " . $keys[$i] . "<br/>";
					}
				}
			// }
		}
	}
	return $results;
}
function merge_tags_array($newTagsArray, $tagsString) {
	if (strlen($tagsString) > 0) {
		$newTagsArray = array_merge(explode(",",$tagsString), $newTagsArray);
	}
	$newTagsArray = array_unique($newTagsArray);
	return $newTagsArray;
}
$limit = 99999;

echo "<B>People</b><Br/>";
$people = $db->query("SELECT * from person WHERE removed = 0;");
//print_r($people);
for($i = 0; $i < count($people) && $i < $limit; $i++) {
	echo $people[$i]['firstname'] . " " . $people[$i]['surnames'] . " - " . $people[$i]['notes'] . "<br/>";
	$tags = create_tags_array($people[$i]['firstname'] . " " . $people[$i]['surnames'], $people[$i]['notes']);
	$tags = merge_tags_array($tags, $people[$i]['tags']);
	print_r($tags);
	echo "<br/><br/>";

	$stmt = $db->prepare("UPDATE person SET tags = :tags WHERE id = :id ;");
	$stmt->bindValue(":tags", implode(",", $tags), Database::VARTYPE_STRING);
	$stmt->bindValue(":id", $people[$i]['id'], Database::VARTYPE_INTEGER);
	$e = $stmt->execute();

}
echo "<br/>";



echo "<b>YouTubers</b><Br/>";
$youtubers = $db->query("SELECT * from youtuber WHERE removed = 0;");
for($i = 0; $i < count($youtubers) && $i < $limit; $i++) {
	echo $youtubers[$i]['name'] . " - " . $youtubers[$i]['notes'] . $youtubers[$i]['description'] . "<br/>";
	$tags = create_tags_array($youtubers[$i]['name'], $youtubers[$i]['notes'] . " " . $youtubers[$i]['description']);
	$tags = merge_tags_array($tags, $youtubers[$i]['tags']);
	print_r($tags);
	echo "<br/><br/>";

	$stmt = $db->prepare("UPDATE youtuber SET tags = :tags WHERE id = :id ;");
	$stmt->bindValue(":tags", implode(",", $tags), Database::VARTYPE_STRING);
	$stmt->bindValue(":id", $youtubers[$i]['id'], Database::VARTYPE_INTEGER);
	$e = $stmt->execute();
	//sleep(1);
}
echo "<br/>";

//die();

echo "<b>Publications</b><Br/>";
$publications = $db->query("SELECT * from publication WHERE removed = 0;");
for($i = 0; $i < count($publications) && $i < $limit; $i++) {
	echo $publications[$i]['name'] . " - " . $publications[$i]['notes'] . "<br/>";
	$tags = create_tags_array($publications[$i]['name'], $publications[$i]['notes']);
	$tags = merge_tags_array($tags, $publications[$i]['tags']);
	print_r($tags);
	echo "<br/><br/>";

	$stmt = $db->prepare("UPDATE publication SET tags = :tags WHERE id = :id ;");
	$stmt->bindValue(":tags", implode(",", $tags), Database::VARTYPE_STRING);
	$stmt->bindValue(":id", $publications[$i]['id'], Database::VARTYPE_INTEGER);
	$e = $stmt->execute();
}
echo "<br/>";

echo "<b>Twitch Channels</b><Br/>";
$twitchchannels = $db->query("SELECT * from twitchchannel WHERE removed = 0;");
for($i = 0; $i < count($twitchchannels) && $i < $limit; $i++) {
	echo $twitchchannels[$i]['name'] . " - " . $twitchchannels[$i]['notes'] . $twitchchannels[$i]['twitchDescription'] . "<br/>";
	$tags = create_tags_array($twitchchannels[$i]['name'], $twitchchannels[$i]['notes'] . " " . $twitchchannels[$i]['twitchDescription']);
	$tags = merge_tags_array($tags, $twitchchannels[$i]['tags']);
	print_r($tags);
	echo "<br/><br/>";

	$stmt = $db->prepare("UPDATE twitchchannel SET tags = :tags WHERE id = :id ;");
	$stmt->bindValue(":tags", implode(",", $tags), Database::VARTYPE_STRING);
	$stmt->bindValue(":id", $twitchchannels[$i]['id'], Database::VARTYPE_INTEGER);
	$e = $stmt->execute();
}
echo "<br/>";

die();
?>
