<?php

set_time_limit(0);
$startTime = time();

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// List of all to-process items.
$queue = $db->query("SELECT * FROM socialqueue WHERE `timestamp` < " . time() . " AND sent = 0 AND removed = 0;");
$queueSize = count($queue);

for($i = 0; $i < $queueSize; $i++) {
	$item = $queue[$i];
	$item['typedata'] = json_decode($item['typedata'], true); 

	if ($item['type'] == "tweet") {
		$message = $item['typedata']['message'];
		$account = db_singleOAuthTwitterById($db, $item['typedata']['account']);

		if (count($item['typedata']['attachments']) > 0) {
			$attachments = $item['typedata']['attachments'];
			for($j = 0; $j < count($attachments); $j++) {
				$attachments[$j] = "images/uploads/" . $attachments[$j];
			}
			$tweet = twitter_postStatusWithImage($account['oauth_key'], $account['oauth_secret'], $message, $attachments);
		} else {
			$tweet = twitter_postStatus($account['oauth_key'], $account['oauth_secret'], $message);
		}
		$item['typedata']['tweet'] = array();
		$item['typedata']['tweet']['id'] = $tweet->id_str;
		$item['typedata']['tweet']['created_at'] = $tweet->created_at;

		$stmt = $db->prepare("UPDATE socialqueue SET typedata = :typedata, sent = :sent WHERE id = :id ");
		$stmt->bindValue(":typedata", json_encode($item['typedata'], JSON_UNESCAPED_UNICODE), Database::VARTYPE_STRING);
		$stmt->bindValue(":sent", 1, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":id", $item['id'], Database::VARTYPE_INTEGER);
		$rs = $stmt->execute();

	} else if ($item['type'] == "retweet") {
		$tweet = $item['typedata']['tweet'];
		$tweet['typedata'] = json_decode($tweet['typedata'], true); 
		
		$otheritem = db_singleSocialQueueItem($db, $tweet);
		$account = db_singleOAuthTwitterById($db, $item['typedata']['account']);	
		
		if (!is_null($otheritem) && 
			!is_null($account) && 
			$otheritem['sent'] && 
			isset($tweet['typedata']['id'])
			) { 
			twitter_retweetStatus($account['oauth_key'], $account['oauth_secret'], $tweet['typedata']['id']);

			$stmt = $db->prepare("UPDATE socialqueue SET sent = :sent WHERE id = :id ");
			$stmt->bindValue(":sent", 1, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":id", $item['id'], Database::VARTYPE_INTEGER);
			$rs = $stmt->execute();
		}

		
	}

	
}


$endTime = time();
echo "Took " . ($endTime - $startTime) . " seconds.";
	
?>