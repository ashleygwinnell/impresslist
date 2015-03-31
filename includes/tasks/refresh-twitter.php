<?php

set_time_limit(0);

// 
// Refresh.php
//
$startTime = time();
$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// People
$people = array();
$people_resultset = $db->query("SELECT * FROM person;");
while($row = $people_resultset->fetchArray()) { $people[] = $row; }
$num_people = count($people);

for($i = 0; $i < count($people); $i++) {
	$twitterAcc = $people[$i]['twitter'];
	if (strlen($twitterAcc) > 0) { 
		$num_followers = twitter_countFollowers($twitterAcc);
		if ($num_followers == "") { $num_followers = 0; }
		if ($num_followers > 0) { 
			$db->query("UPDATE person SET twitter_followers='" . $num_followers . "' WHERE id = '" . $people[$i]['id'] . "';");
		}
	}
}

// Publications
$publications = array();
$publications_resultset = $db->query("SELECT * FROM publication;");
while($row = $publications_resultset->fetchArray()) { $publications[] = $row; }
$num_publications = count($publications);

for($i = 0; $i < count($publications); $i++) {
	$twitterAcc = $publications[$i]['twitter'];
	if (strlen($twitterAcc) > 0) { 
		$num_followers = twitter_countFollowers($twitterAcc);
		if ($num_followers == "") { $num_followers = 0; }
		if ($num_followers > 0) { 
			$db->query("UPDATE publication SET twitter_followers='" . $num_followers . "' WHERE id = '" . $publications[$i]['id'] . "';");
		}
	}
}

//header("Location: /");
//die();
echo "done!<br/>";
$endTime = time();
echo "took " . ($endTime - $startTime) . " seconds.";
	
?>