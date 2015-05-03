<?php

set_time_limit(0);

// 
// Refresh Youtuber subs/views count.php
//
$startTime = time();
$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// People
$youtubeChannels = $db->query("SELECT * FROM youtuber WHERE lastpostedon_updatedon < " . (time()-3600) . " AND removed = 0;");
$num_youtubeChannels = count($youtubeChannels);

$twittersUpdated = 0;
$subscriptionsUpdated = 0;
for($i = 0; $i < $num_youtubeChannels; $i++) 
{
	$youtubeChannel = $youtubeChannels[$i]['channel'];
	// echo $youtubeChannel;
	if (strlen($youtubeChannel) > 0) 
	{
		$youtubeDetails = youtube_getInformation($youtubeChannel);
		if ($youtubeDetails != 0) { 
			echo "updated " . $content['entry']['title']['$t'] . "<br/>";
			$result = array(
				"name" => $content['entry']['title']['$t'],
				"description" => strip_tags($content['entry']['content']['$t']),
				"lastpostedon" => strtotime($content['entry']['updated']['$t']),
				"iconurl" => $content['entry']['media$thumbnail']['url'],
				"subscribers" => $content['entry']['yt$statistics']['subscriberCount'],
				"views" => $content['entry']['yt$statistics']['totalUploadViews']
			);

			$db->exec("UPDATE youtuber 
							SET 
								lastpostedon = '" . $youtubeDetails['lastpostedon'] . "', 
								lastpostedon_updatedon = '" . time() . "', 
								subscribers = '" . $youtubeDetails['subscribers'] . "', 
								views = '" . $youtubeDetails['views'] . "' 
							WHERE id = '" . $youtubeChannels[$i]['id'] . "';");
			$subscriptionsUpdated++;
			sleep(1);
		}
	}

	
	// echo "<hr/>";
}

//header("Location: /");
//die();
echo "num youtubers: " . $num_youtubeChannels . "<br/>";
//echo "updated " . $twittersUpdated . " twitter accounts<br/>";
echo "updated " . $subscriptionsUpdated . " youtuber subscriptions <br/>";
echo "done!<br/>";
$endTime = time();
echo "took " . ($endTime - $startTime) . " seconds.";
	
?>