<?php

set_time_limit(0);
//set_time_limit(5);

// 
// Refresh.php
//
$startTime = time();
$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// People
$people = $db->query("SELECT * FROM person WHERE twitter_updatedon < " . (time()-3600) . " AND removed = 0;");
$num_people = count($people);

for($i = 0; $i < count($people); $i++) {
	$twitterAcc = $people[$i]['twitter'];
	if (strlen($twitterAcc) > 0) { 
		$num_followers = twitter_countFollowers($twitterAcc);
		if ($num_followers == "") { $num_followers = 0; }
		if ($num_followers > 0) { 
			echo "updating person " . $people[$i]['id'] . "<br/>";

			$db->exec("UPDATE person SET 
						twitter_followers='" . $num_followers . "', 
						twitter_updatedon='" . time() . "' 
						WHERE id = '" . $people[$i]['id'] . "';");
			sleep(1);
		}
	}
}

// Publications
$publications = $db->query("SELECT * FROM publication WHERE twitter_updatedon < " . (time()-3600) . " AND removed = 0;");
$num_publications = count($publications);

for($i = 0; $i < count($publications); $i++) {
	$twitterAcc = $publications[$i]['twitter'];
	if (strlen($twitterAcc) > 0) { 
		$num_followers = twitter_countFollowers($twitterAcc);
		if ($num_followers == "") { $num_followers = 0; }
		if ($num_followers > 0) { 
			echo "updating publication " . $publications[$i]['id'] . "<br/>";

			$db->exec("UPDATE publication SET 
						twitter_followers='" . $num_followers . "', 
						twitter_updatedon='" . time() . "' 
						WHERE id = '" . $publications[$i]['id'] . "';");
			sleep(1);
		}
	}
}

// Youtubers
$youtubeChannels = $db->query("SELECT * FROM youtuber WHERE twitter_updatedon < " . (time()-3600) . " AND removed = 0;");
$num_youtubeChannels = count($youtubeChannels);

for($i = 0; $i < count($youtubeChannels); $i++) 
{
	$twitterAcc = $youtubeChannels[$i]['twitter'];
	if (strlen($twitterAcc) > 0) 
	{
		$num_followers = twitter_countFollowers($twitterAcc);
		if ($num_followers == "") { $num_followers = 0; }
		if ($num_followers > 0) { 
			echo "updating youtuber " . $youtubeChannels[$i]['id'] . "<br/>";

			$db->exec("UPDATE youtuber 
						SET 
							twitter_followers = '" . $num_followers . "',
							twitter_updatedon = '" . time() . "' 
						WHERE id = '" . $youtubeChannels[$i]['id'] . "';");
			//$twittersUpdated++;
			sleep(1);
		}
	}
}

//header("Location: /");
//die();
echo "done!<br/>";
$endTime = time();
echo "took " . ($endTime - $startTime) . " seconds.";
	
?>