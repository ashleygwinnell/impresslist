<?php

// Potential OR proper depending on game settings.
function coverage_tryAddYoutubeCoverageUnsure(
		$game,
		$watchedGame,
		$youtuberDbId,
		$youtuberChannelId,
		$youtuberChannelName,
		$videoId,
		$videoTitle,
		$videoDescription,  // this is new
		$videoThumbnail,
		$videoTime,
		$verbose=true)
{
	global $db;

	// #####
	$url = "https://www.youtube.com/watch?v=".$videoId;
	if ($videoThumbnail == null) { $videoThumbnail = ""; }

	$gameId = null;
	if ($game !== null) {
		$gameId = $game['id'];
	}
	$watchedGameId = null;
	if ($watchedGame !== null) {
		$watchedGameId = $watchedGame['id'];
	}

	// Make sure it's in the time limit.
	if ($game !== null) {
		if ($game['coverageOnlyAfterUtime'] > 0) {
			if ($videoTime < $game['coverageOnlyAfterUtime']) {
				echo "<i>It was published before the required time.</i><br/>\n";
				echo $videoTime . " is less than " . $game['coverageOnlyAfterUtime'] . "<br/>\n";
				return;
			}
		}

		// if ($game['usesBlacklist'])
			// check blacklist?
		// }
	}

	if (youtuber_channel_id_blacklisted($youtuberChannelId)) {
		echo "<i>Channel " . $youtuberChannelName . " (" . $youtuberChannelId . ") is blacklisted.</i><br/>\n";
		return;
	}

	$gameCheck = "";
	$gameCheckNoun = "";
	if ($gameId != null) {
		$gameCheck = " AND game = :game ";
	} else if ($watchedGameId != null) {
		$gameCheck = " AND watchedgame = :watchedgame ";
	}
	$addGameCheck = function($stmt) use ($gameCheck, $gameId, $watchedGameId) {
		if (strlen($gameCheck) > 0) {
			if ($gameId != null) {
				$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
			}
			else if ($watchedGameId != null) {
				$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
			}
		}
	};

	// Existing coverage.
	$stmt = $db->prepare("SELECT * FROM youtuber_coverage WHERE url = :url " . $gameCheck . " LIMIT 1;");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$addGameCheck($stmt);
	$existingCoverage = $stmt->query();

	if (count($existingCoverage) > 0) {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database.</i>: " . $gameCheck . "<br/>\n";
		print_r($game);
		// debug_print_backtrace();
		return;
	}

	// Existing coverage to be approved.
	$stmt = $db->prepare("SELECT * FROM youtuber_coverage_potential WHERE url = :url " . $gameCheck . " LIMIT 1;");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$addGameCheck($stmt);
	$existingPotentialCoverage = $stmt->query();

	if (count($existingPotentialCoverage) > 0) {
		// add potential.
		echo $existingPotentialCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database (potential).</i><br/>\n";
		return;
	}

	$videoTitle = remove_emoji_from_string($videoTitle);

	$summary = array(
		"id"   => $videoId,
		"youtuber_id" => $youtuberDbId,
		"url"  => $url,
		"title"   => $videoTitle,
		"thumbnail" => $videoThumbnail,
		"description"  => $videoDescription,
		"published_on" => $videoTime,
		"channel_id"   => $youtuberChannelId,
		"channel_title" => $youtuberChannelName
	);

	$requiresApproval = false; // default to false as watched games do not need approval for adding.
	if ($gameId != null) {
		$game = db_singlegame($db, $gameId);
		$requiresApproval = $game['coverageRequiresApproval'];
	}

	if ($verbose) {
		echo "Adding coverage " . (($requiresApproval)?"potential":""). " from {$youtuberChannelName}<br/>\n";
		echo $url . "<br/>\n";
		echo $videoTitle . "<br/>\n";
		echo $videoTime . "<br/>\n";
		echo $videoThumbnail . "<br/>\n";
		echo "<hr/>\n";
	}

	// add to potentials
	if ($requiresApproval) {
		$potential_id = youtuber_coverage_potential_add($gameId, $watchedGameId, $summary);
		if ($verbose) {
			echo "<b>Added Potential Coverage:</b> (id " . $potential_id . ") - " . $videoTitle . " - " . $url . "<br/>\n";
		}
		return $potential_id;
	}
	// add to approved
	else {
		$latest_id = youtuber_coverage_add($gameId, $watchedGameId, $summary);
		if ($verbose) {
			echo "<b>Added Potential Coverage:</b> (id " . $latest_id . ") - " . $videoTitle . " - " . $url . "<br/>\n";
		}

		return $latest_id;
	}

}

function twitchchannel_coverage_potential_approve($potential, $audience, $notes, $sendAlert = true) {
	global $db;

	$gameId = $potential['game'];
	$twitchChannelId = $potential['twitchChannelId'];

	$game = db_singlegame($db, $gameId);

	$results = $db->query("SELECT * FROM twitchchannel WHERE twitchId = '" . $twitchChannelId . "' AND removed = 0 LIMIT 1;");
	if (count($results) == 0) {
		// echo "not in database. adding...<br/>";
		$result = tryAddTwitchChannel($twitchChannelId, $audience, $notes);
		if ($result === FALSE) {
			return false;
		}
		// echo "added...<br/>";
		$twitchchannel = $result;
		// // get the account info
		// $error = "";
		// $youtuberInfo = youtube_v3_getInformation($youtuberChannelId, $error);
		// if ($youtuberInfo == 0) {
		// 	$result = api_error("Youtube channel '" . $youtuberChannelId . "' not found. " . $error, BotErrorCode::YOUTUBER_NOT_FOUND);
		// 	api_result($result);
		// 	die();
		// }
		// // print_r($youtuberInfo);

		// // We have to add the YouTuber! AGH!
		// $youtuber_id = youtuber_add($potential['channelTitle'], $youtuberInfo['description'], $audience, $youtuberChannelId, $youtuberInfo['iconurl'], "".$youtuberInfo['subscribers'], "".$youtuberInfo['views'], "".$youtuberInfo['videos'], $notes);
		// if ($youtuber_id === FALSE) {
		// 	return FALSE; //api_result(api_error("mysqli error" . $stmt->error, BotErrorCode::DATA_ERROR));
		// }
		// $youtuber = db_singleyoutubechannel($db, $youtuber_id);
	}
	else {
		$twitchchannel = $results[0];
	}

	if ($twitchchannel) {

		$game = db_singlegame($db, $potential['game']);

		$coverage_id = tryAddTwitchCoverage(
			$game['company'],						// $companyId,
			$twitchchannel['id'],					// $myChannelId,
			$potential['twitchChannelId'],			// $twitchChannelId,
			$potential['twitchChannelName'],		// $twitchChannelName,
			$potential['twitchVideoId'], 			// $twitchVideoId,
			$potential['twitchClipId'], 			// $twitchClipId
			$potential['game'],						// $gameId
			$game['name'],							// $gameName
			$potential['url'],						// $url
			$potential['title'],					// $title
			$potential['description'],				// $description
			$potential['thumbnail'],				// $thumbnail,
			$potential['utime'],					// $time
			false, 									// verbose
			$sendAlert 								// $sendAlert
		);

		// Link the "potential" to the "final" coverage.
		$stmt = $db->prepare("UPDATE twitchchannel_coverage_potential SET coverage = :coverage_id, removed = 1 WHERE id = :id LIMIT 1; ");
		$stmt->bindValue(":id", $potential['id'], Database::VARTYPE_INTEGER);
		$stmt->bindValue(":coverage_id", $coverage_id, Database::VARTYPE_INTEGER);
		$stmt->execute();

		return true;
	}
	return false;
}

function twitchchannel_coverage_potential_reject($coverageItem) {
	global $db;
	$stmt = $db->prepare("UPDATE twitchchannel_coverage_potential SET removed = 1 WHERE id = :id LIMIT 1; ");
	$stmt->bindValue(":id", $coverageItem['id'], Database::VARTYPE_INTEGER);
	$e = $stmt->execute();
	return $e;
}

function tryAddTwitchCoverage($companyId, $myChannelId, $twitchChannelId, $twitchChannelName, $twitchVideoId, $twitchClipId, $gameId, $gameName, $url, $title, $description, $thumbnail, $time, $verbose = true, $sendAlert = true) {
	global $db;

	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	if ($verbose) {
		echo "Found Coverage!<br/>\n";
	}
	if (!twitch_coverage_exists($url, $gameId)) {

		if ($verbose) {
			echo "Adding coverage from {$twitchChannelName}<br/>\n";
			echo $title . "<br/>\n";
			echo $description . "<br/>\n";
			echo $url . "<br/>\n";
			echo $time . "<br/>\n";
			echo $thumbnail . "<br/>\n";
			echo "<hr/>\n";
		}

		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO twitchchannel_coverage (`id`, `twitchchannel`, `twitchVideoId`, `twitchClipId`, `twitchChannelId`, `game`, `url`, `title`, `description`, `thumbnail`, `utime`, `thanked`, `removed`)
														VALUES (NULL, :twitchchannel, :twitchVideoId, :twitchClipId, :twitchChannelId, :game, :url, :title, :description, :thumbnail, :utime, 0, 0 ); ");
		$stmt->bindValue(":twitchchannel", $myChannelId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchVideoId", $twitchVideoId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchClipId", $twitchClipId, Database::VARTYPE_STRING);
		$stmt->bindValue(":twitchChannelId", $twitchChannelId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":description", $description, Database::VARTYPE_STRING);
		$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$res = $stmt->execute();

		if (!$res) {
			return FALSE;
		}
		if ($sendAlert) {
			@email_new_coverage($companyId, $gameName, $twitchChannelName, $title, $url, $time);
			@slack_coverageAlert($companyId, $gameName, $twitchChannelName, $title, $url);
			@discord_coverageAlert($companyId, $gameName, $twitchChannelName, $title, $url);
		}

		return $db->lastInsertRowID();

	} else {
		if ($verbose) {
			echo $url . "<br/>\n";
			echo "<i>It was already in the database (twitch url: ". $url . ").</i><br/>\n";
		}
		return FALSE;
	}
}

function tryAddTwitchPotentialCoverage($companyId, $twitchChannelId, $twitchChannelName, $twitchVideoId, $twitchClipId, $gameId, $gameName, $url, $title, $description, $thumbnail, $time) {
	global $db;
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	echo "Found Potential Coverage!<br/>\n";
	if (!twitch_coverage_potential_exists($url, $gameId)) {

		echo "Adding coverage from {$twitchChannelName}<br/>\n";
		echo "Title: " . $title . "<br/>\n";
		echo "Description: " . $description . "<br/>\n";
		echo "Url: " . $url . "<br/>\n";
		echo "Time: " . $time . "<br/>\n";
		echo "Thumbnail: " . $thumbnail . "<br/>\n";
		echo "<hr/>\n";

		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO twitchchannel_coverage_potential (`id`, `twitchVideoId`, `twitchClipId`, `twitchChannelId`, `twitchChannelName`, `game`, `url`, `title`, `description`, `thumbnail`, `utime`, `removed`)
																	VALUES (NULL, :twitchVideoId, :twitchClipId, :twitchChannelId, :twitchChannelName, :game, :url, :title, :description, :thumbnail, :utime, 0 ); ");
		$stmt->bindValue(":twitchVideoId", $twitchVideoId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchClipId", $twitchClipId, Database::VARTYPE_STRING);
		$stmt->bindValue(":twitchChannelId", $twitchChannelId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":twitchChannelName", $twitchChannelName, Database::VARTYPE_STRING);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":description", $description, Database::VARTYPE_STRING);
		$stmt->bindValue(":thumbnail", $thumbnail, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$e = $stmt->execute();
		if (!$e) {
			echo "Could not add!";
		}
	} else {
		echo $url . "<br/>\n";
		echo "<i>It was already in the database (twitch potential).</i><br/>\n";
	}
}



// Potential OR proper depending on game settings.
function tryAddTwitchCoverageUnsure(
		$game,
		$dbchannel,
		$twitchChannelId,
		$twitchChannelName,
		$twitchVideoId,
		$twitchClipId,
		$url,
		$title,
		$description,
		$thumbnail,
		$time
	) {

	$gameId = $game['id'];
	$gameName = $game['name'];
	$gameCompany = $game['company'];
	if ($game['coverageRequiresApproval'] || !$dbchannel) {
		// $companyId, $twitchChannelId, $twitchChannelName, $twitchVideoId, $twitchClipId, $gameId, $gameName, $url, $title, $description, $thumbnail, $time
		tryAddTwitchPotentialCoverage(
			$gameCompany,
			$twitchChannelId,
			$twitchChannelName,
			$twitchVideoId,	// video id
			$twitchClipId, 	// clip id
			$gameId,
			$gameName,
			$url,
			$title,
			$description,
			$thumbnail,
			$time
		);
	}
	else {
		// $companyId, $myChannelId, $twitchChannelId, $twitchChannelName, $twitchVideoId, $twitchClipId, $gameId, $gameName, $url, $title, $description, $thumbnail, $time
		tryAddTwitchCoverage(
			$gameCompany,
			$dbchannel['id'],
			$twitchChannelId,
			$twitchChannelName,
			$twitchVideoId, // video id
			$twitchClipId, 	// clip id
			$gameId,
			$gameName,
			$url,
			$title,
			$description,
			$thumbnail,
			$time,
			true, // verbose
			true // sendalert
		);
	}
}


function tryAddTwitchChannel($channelOrUserId, $audience = 1, $notes = "") {
	global $db;
	//echo $channelOrUserId;
	$channels = $db->query("SELECT * FROM twitchchannel WHERE twitchId = {$channelOrUserId} LIMIT 1");
	if (count($channels) == 0) {
		// Add Channel.
		$users = twitch_getUsers($channelOrUserId);
		if ($users['data'] && count($users['data']) == 1) {
			$user = $users['data'][0];

			$success = db_try_add_twitch_channel_from_user_result( $user, $audience, $notes );
			if ($success) {
				$id = $db->lastInsertRowID();
				$channels = $db->query("SELECT * FROM twitchchannel WHERE id = {$id} LIMIT 1");
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}
	return $channels[0];
}




function tryAddPublicationCoverage(
		$companyId,
		$publicationId,
		$publicationName,
		$gameId,
		$gameName,
		$watchedGameId,
		$watchedGameName,
		$title,
		$url,
		$time)
{
	// echo $publicationId . "<br/>" .
	// 	 $publicationName . "<br/>" .
	// 	 $gameId . "<br/>" .
	// 	 $watchedGameId . "<br/>" .
	// 	 $title . "<br/>" .
	// 	 $url. "<br/>" .
	// 	 $time . "<br/>";
	//die('trying');
	global $db;
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	// TODO: we can't have the same game name mentioned twice.

//#####
	$gameCheck = "";
	if ($gameId != null) {
		$gameCheck = " AND game = :game ";
	} else if ($watchedGameId != null) {
		$gameCheck = " AND watchedgame = :watchedgame ";
	}
	$addGameCheck = function($stmt) use ($gameCheck, $gameId, $watchedGameId) {
		if (strlen($gameCheck) > 0) {
			if ($gameId != null) {
				$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
			}
			else if ($watchedGameId != null) {
				$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
			}
		}
	};

	echo "Found Coverage for <b>{$gameName} {$watchedGameName}</b>!<br/>\n";
	$stmt = $db->prepare("SELECT * FROM publication_coverage WHERE url = :url " . $gameCheck . " LIMIT 1; ");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$addGameCheck($stmt);
	$existingCoverage = $stmt->query();
	if (count($existingCoverage) == 0) {

		$approved = 1;
		if ($gameId != null) {
			$game = db_singlegame($db, $gameId);
			$approved = ($game['coverageRequiresApproval']==1)?0:1;
		}

		echo "Adding coverage from {$publicationName}<br/>\n";
		echo $title . "<br/>\n";
		echo $url . "<br/>\n";
		echo $time . "<br/>\n";
		echo "<hr/>\n";
		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO publication_coverage (id, publication, person, game, watchedgame, url, title, `utime`, approved)
														VALUES (NULL, :publication, NULL, :game, :watchedgame, :url, :title, :utime, :approved ); ");
		$stmt->bindValue(":publication", $publicationId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":approved", $approved, Database::VARTYPE_INTEGER);
		$stmt->execute();

		if ($watchedGameId == null && $companyId != 0 && $approved) {
			@email_new_coverage($companyId, $gameName, $publicationName, $title, $url, $time);
			@slack_coverageAlert($companyId, $gameName, $publicationName, $title, $url);
			@discord_coverageAlert($companyId, $gameName, $publicationName, $title, $url);
		}
		return true;
	} else {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database (pub cov).</i><br/>\n";
		// debug_print_backtrace();
		return false;
	}
}
function publication_coverage_approve($coverageItem, $sendAlert = true) {
	global $db;
	$stmt = $db->prepare("UPDATE publication_coverage SET approved = 1, removed = 0 WHERE id = :id LIMIT 1; ");
	$stmt->bindValue(":id", $coverageItem['id'], Database::VARTYPE_INTEGER);
	$success = $stmt->execute();

	if ($success) {
		$game = db_singlegame($db, $coverageItem['game']);
		$gameName = $game['name'];
		$company = db_singlecompany($db, $game['company']);
		$companyId = $company['id'];
		$publication = db_singlepublication($db, $coverageItem['publication']);
		$publicationName = $publication['name'];
		$title = $coverageItem['title'];
		$url = $coverageItem['url'];
		$time = $coverageItem['utime'];

		if ($sendAlert) {
			@email_new_coverage($companyId, $gameName, $publicationName, $title, $url, $time);
			@slack_coverageAlert($companyId, $gameName, $publicationName, $title, $url);
			@discord_coverageAlert($companyId, $gameName, $publicationName, $title, $url);
		}
	}
	return $success;
}

function publication_coverage_reject($coverageItem) {
	global $db;
	$stmt = $db->prepare("UPDATE publication_coverage SET approved = 0, removed = 1 WHERE id = :id LIMIT 1;");
	$stmt->bindValue(":id", $coverageItem['id'], Database::VARTYPE_INTEGER);
	return $stmt->execute();
}

function publication_coverage_manual_submit($fullUrl, &$errorMessage = "") {
	global $db;

	$endbaseUrl = strpos($fullUrl, "/", 8);

	if ($endbaseUrl !== FALSE) {
		$baseUrl = substr($fullUrl, 0, $endbaseUrl+1);

		$stmt = $db->prepare("SELECT * FROM publication WHERE url LIKE :url AND removed = 0 LIMIT 1; ");
		$stmt->bindValue(":url", $baseUrl . "%", Database::VARTYPE_STRING);
		$publications = $stmt->query();
		// print_r($publications);
		if (count($publications) != 1) {
			//die("Could not find publication with base url: " . $baseUrl);
			// TODO: check without trailing slash, and with http://
			$errorMessage = "Could not find publication with base url: " . $baseUrl;
			return false;
		}
		else {
			// Games
			$games = $db->query("SELECT * FROM game WHERE removed = 0;");
			$num_games = count($games);

			// Watched Games
			$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
			$num_watchedgames = count($watchedgames);

			$publication = $publications[0];
			$success = coverage_scrapePublicationUrl($games, $watchedgames, $publication, $fullUrl, false, true, $errorMessage);
			return $success;
		}
	}
	return false;
}

function _fixrelativeurl($host, $url) {
	if (substr($url, 0, 1) == "/") {
		if (substr($host, -1, 1) == "/") {
			return $host . substr($url, 1);
		}
		return $host . $url;
	}
	return $url;
}

// return true if there contents are valid.
function coverage_scrapeArticleContents($games, $watchedgames, $publication, $url, $title, $pubDate, $contents, $doAdd = true) {
	$articlecontents_lc = strtolower($contents);
	$articlecontents_lc = util_cleanHtmlArticleContents($url, $articlecontents_lc); //util_cleanhtml($articlecontents_lc);

	if (strlen($articlecontents_lc) == 0) {
		echo "cleaned content was empty... ({$url}) <br/>\n";
	}
	else {
		// HACKS: don't look after common "end of blog" lines like "related articles".
		$strip_after_common_strings = array(
			"related",
			"related articles",
			"related items",
			"more from author",
			"recent mmo crowdfunding news",
			"ça vous a intéressé ? en voilà encore !",
			"Previous post",
			"For more news be sure to read next"
		);
		$strip_from_potentials = array();
		for ($stripindex = 0; $stripindex < count($strip_after_common_strings); $stripindex++) {
			$potential_strip_index = strpos($articlecontents_lc, $strip_after_common_strings[$stripindex]);
			if ($potential_strip_index > 0) {
				$strip_from_potentials[] = $potential_strip_index;
			}
		}
		if (count($strip_from_potentials) > 0) {
			$articlecontents_lc = substr($articlecontents_lc, 0, min($strip_from_potentials));
		}



		foreach ($games as $game) {
			$matches = array();
			$contains = util_muddyCoverageContains($articlecontents_lc, $game['name'], $game['keywords']);
			$containsBlackwords = util_containsKeywords($articlecontents_lc, $game['blackwords'], $matches);

			if ($contains) {
				if (!$containsBlackwords) {
					if ($doAdd) {
						tryAddPublicationCoverage($game['company'], $publication['id'], $publication['name'], $game['id'], $game['name'], null, "", $title, $url, $pubDate );
					}
					else {
						echo "Found game ". $game['name'] . " in " . $title . " (" . $url . ") but add is disabled.<br/>\n";
					}
				} else {
					echo "Found Coverage for <b>" . $game['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
					print_r($matches);
				}
			}
		}
		foreach($watchedgames as $watchedgame) {

			$matches = array();
			$contains = util_muddyCoverageContains($articlecontents_lc, $watchedgame['name'], $watchedgame['keywords']);
			$containsBlackwords = util_containsKeywords($articlecontents_lc, $watchedgame['blackwords'], $matches);

			if ($contains) {
				if (!$containsBlackwords) {
					if ($doAdd) {
						tryAddPublicationCoverage(0, $publication['id'], $publication['name'], null, "", $watchedgame['id'], $watchedgame['name'], $title, $url, $pubDate );
					} else {
						echo "Found watched game ". $watchedgame['name'] . " in " . $title . " (" . $url . ") but add is disabled.<br/>\n";
					}
				} else {
					echo "Found Coverage for watched game <b>" . $watchedgame['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
					print_r($matches);
				}

				//print_r($articlecontents_lc);
				//die();
			}
		}

		return true;
	}
	return false;
}

// returns true on success.
function coverage_scrapePublicationRSS($games, $watchedgames, $publication, $doAdd = true, &$rssError = "") {
	$max_url_scrapes_per_publication = 50;
	// Scrape RSS feed.
	// $doScrape = true;
	$rss = $publication['rssfeedurl'];
	if (strlen($rss) > 0)
	{
		echo "Checking RSS... (" .$rss . ").  <br/>\n";

		// Use XML parser on the feed.
		$rsscontent = url_get_contents($rss);
		// $doc = new DOMDocument();
		// $doc->strictErrorChecking = false;
		// @$doc->loadXML( $rsscontent );
		// $xml = @simplexml_import_dom($doc);
		$xml = @simplexml_load_string($rsscontent);
		if ($xml === FALSE) {
			// log error.
			echo "Invalid XML for website .<br/>\n";
			$rssError = "Invalid RSS/XML - false";
			// TODO: we want to set a warning flag on this RSS validity.
			//continue;
		}
		else if (!is_object($xml)) {
			echo "Invalid XML for website. Did not make XML object.<br/>\n";
			$rssError = "Invalid RSS/XML - not an object";
			// TODO: we want to set a warning flag on this RSS validity.
			//continue;
		}
		// TODO: do we want to check the *length* of the rss feed? if it's super long we might want to blacklist it for bandwidth reasons...
		else {
			$items = $xml->channel->item;
			//$items = $xml->body->rss->channel->item;
			//print_r($xml);

			if ($items == null) {
				//print_r($xml);
				echo "Skipping...<br/>\n";
				$rssError = "Empty feed";
				// TODO: we want to set a warning flag on this RSS validity.
				//continue;
			} else {

				$countUrlScrapes = 0;

				foreach ($items as $item) {

					$title = htmlentities($item->title);
					$description = htmlentities($item->description);
					$time = strtotime($item->pubdate);
					if (!$time && strtotime($item->pubDate)) {
						$time = strtotime($item->pubDate);
					}
					if ($time == 0) {
						$time = time();
					}


					$url = $item->link->__toString();
					$oldurl = $url;
					if (strlen($url) == 0) {
						$url = $item->guid;
						// isPermaLink
					}

					//echo "title: " . $title . "<br/>\n";
					//echo "time: " . $time . "<br/>\n";
					//echo "$item->pubdate: " . $item->pubDate . "<br/>\n";

					//print_r($item);
					// echo "description: " . $description . "<br/>\n";
					// echo "url: " . $url . "<br/>\n";
					// echo "oldurl: " . $oldurl . "<br/>\n";
					// echo "link: " . $item->link . "<br/>\n";
					// echo "link: " . ((string)$item->link) . "<br/>\n";

					// print_r($item);
					echo $url . "<br/>\n";

					// Scan titles and descriptions
					foreach ($games as $game)
					{
						$matchesBlackwords = array();
						$titleContainsGame = strpos(strtolower($title), strtolower($game['name'])) !== FALSE || util_containsKeywords($title, $game['keywords']);
						$descriptionContainsGame = strpos(strtolower($description), strtolower($game['name'])) !== FALSE || util_containsKeywords($description, $game['keywords']);
						$articleContainsBlackwords = util_containsKeywords($title, $game['blackwords']) || util_containsKeywords($description, $game['blackwords'], $matchesBlackwords);

						if ($titleContainsGame || $descriptionContainsGame) {
							if (!$articleContainsBlackwords) {
								if ($doAdd) {
									tryAddPublicationCoverage($game['company'], $publication['id'], $publication['name'], $game['id'], $game['name'], null, "", $title, $url, $time );
								}
								else {
									echo "Found game ". $game['name'] . " in " . $title . " (" . $url . ") but add is disabled.<br/>\n";
								}
							} else {
								echo "Found Coverage for <b>" . $game['name'] . "</b> (" . $game['id'] . ") ({$url}) - but it contained a blackword!<br/>\n";
								print_r($matchesBlackwords);
							}
						}
					}
					foreach($watchedgames as $watchedgame) {
						$matchesBlackwords = array();
						$titleContainsGame = strpos(strtolower($title), strtolower($watchedgame['name'])) !== FALSE || util_containsKeywords($title, $watchedgame['keywords']);
						$descriptionContainsGame = strpos(strtolower($description), strtolower($watchedgame['name'])) !== FALSE || util_containsKeywords($description, $watchedgame['keywords']);
						$articleContainsBlackwords = util_containsKeywords($title, $watchedgame['blackwords']) || util_containsKeywords($description, $watchedgame['blackwords'], $matchesBlackwords);

						if ($titleContainsGame || $descriptionContainsGame) {
							if (!$articleContainsBlackwords) {
								if ($doAdd) {
									tryAddPublicationCoverage(0, $publication['id'], $publication['name'], null, "", $watchedgame['id'], $watchedgame['name'], $title, $url, $time );
								}
								else {
									echo "Found watched game ". $watchedgame['name'] . " in " . $title . " (" . $url . ") but add is disabled.<br/>\n";
								}
							} else {
								echo "Found Coverage for watched game <b>" . $watchedgame['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
								print_r($matchesBlackwords);
							}
						}
					}

					// Scan each rss article contents!
					$urlhash = util_publication_url_hash($publication['id'], $url);
					$alreadyScraped = util_publication_url_hash_exists($urlhash);
					if ($alreadyScraped) {
						echo "Already scraped " . $url . "<br/>\n";
					}
					else {
						$articlecontents = url_get_contents($url);
						$countUrlScrapes++;
						if (strlen($articlecontents) == 0) {
							echo $url . "  was empty... <br/>\n";
						} else {

							$hasContents = coverage_scrapeArticleContents($games, $watchedgames, $publication, $url, $title, $time, $articlecontents, true);
							if ($hasContents) {
								util_publication_url_hash_insert($urlhash);
							}
						}
						if ($countUrlScrapes >= $max_url_scrapes_per_publication) {
							break;
						}
					}


				}
				return true;
				// $doScrape = false;
				//echo $rsscontent;
				//continue;
			}
		}
	}
	return false;
}

function coverage_scrapePublicationHomepage($games, $watchedgames, $publication, $doAdd = true, &$pageError = "") {
	echo "Scraping homepage...<br/>\n";
	$url = $publication['url'];
	coverage_scrapePublicationUrl($games, $watchedgames, $publication, $url, true, $doAdd, $pageError);
}
function coverage_scrapePublicationUrl($games, $watchedgames, $publication, $url, $checkLinks = true, $doAdd = true, &$pageError = "") {
	echo "Scraping url: " . $url . "...<br/>\n";

	//die("2" . $_SERVER['DOCUMENT_ROOT']);
	if (strlen(trim($url)) > 0) {

		$urlcontents = url_get_contents($url);
		if (strlen($urlcontents) == 0) {
			echo "Could not get contents of homepage. Skipping...<br/>\n";
			$pageError = "Could not get page contents";
			return false;
			//die();
			//continue;
		} else {

			//die('ha');
			echo "Reading url...<br/>\n";

			$doc = new DOMDocument();
			$doc->strictErrorChecking = false;
			@$doc->loadHTML( $urlcontents );
			$xml = simplexml_import_dom($doc);
			if ($xml == null) {
				echo "XML error - could not scrape page. Attempting regex scrape...<br/>\n";

				$pageError = "Could not parse page as XHTML.";

				$derp = preg_match('/<a href="(.+)">/', $urlcontents, $match);
				$info = parse_url($match[1]);
				echo $info['scheme'].'://'.$info['host']; // http://www.mydomain.com
				return false;

			//	continue;
			}
			else
			{
				echo "Got page contents...<br/>\n";

				// Checking sublinks
				// $checkedUrls = array();

				// This function reads the DOM and looks for links with the game names.
				// TODO: it should look for the keywords also!
				$checkForGameOrWatchedGame = function($companyId, $name, $name_safe, $gameId, $gameCoverageRequireApproval, $watchedGameId) use ($xml, $publication, $doAdd, $url//, $checkedUrls
				) {

					// match any links that contain the game/watchedgame name
					$arr = $xml->xpath('//a[contains(concat(" ", @href, " "), "' . $name_safe . '")] | //a[contains(concat(" ", @title, " "), "' . $name . '")]');

					//print_r($arr);

					// make sure each url only appears once.
					$checkedUrls = array();
					$simulateNoTitle = false;
					foreach ($arr as $item) {

						$__attrs = $item->attributes();
						$href = $__attrs['href'];
						if (count($checkedUrls) == 0) {
							//echo "here " . $url . $href->__toString() . "<br/>\n";
							$obj = array();
							$obj['url'] = _fixrelativeurl($url, $href->__toString());
							$obj['time'] = time();
							if (isset($__attrs['title'])) {
								$obj['title'] = htmlentities($__attrs['title']->__toString());
							}
							$obj['company'] = $companyId;
							$checkedUrls[] = $obj;
						} else {
							for($j = 0; $j < count($checkedUrls); $j++) {
								if ($checkedUrls[$j]['url'] == _fixrelativeurl($url, $href->__toString())) {
									if (!isset($checkedUrls[$j]['title']) && isset($__attrs['title'])) {
										$checkedUrls[$j]['title'] = htmlentities($__attrs['title']);
									}
								}
							}
						}

					}
					if ($simulateNoTitle) {
						foreach ($checkedUrls as $key => $checked) {
							if (isset($checkedUrls[$key]['title'])) {
								unset($checkedUrls[$key]['title']);
							}
						}
					}
					// For links without titles, fetch titles.
					foreach ($checkedUrls as $key => $checked) {
						if (!isset($checkedUrls[$key]['title'])) {
							try {
								$url2 = _fixrelativeurl($url, $checkedUrls[$key]['url']);
								$url2contents = url_get_contents($url2);
								$doc2 = new DOMDocument();
								$doc2->strictErrorChecking = false;
								@$doc2->loadHTML( $url2contents );
								$xml2 = simplexml_import_dom($doc2);
								if ($xml2 == null) {
									echo "Cannot parse inner link html...<br/>\n";

									$derp = preg_match('/<title>/', $url2contents, $match);
									$info = parse_url($match[1]);
									echo $info['scheme'].'://'.$info['host']; // http://www.mydomain.com
								} else {
									$checkedUrls[$key]['title'] = htmlentities($xml2->head->title->__toString());
								}
							} catch (Exception $e) {
								echo $e;
							}
						}
					}

					// TODO: how the heck is this working?
					// add to database!
					if ($doAdd) {
						foreach ($checkedUrls as $key => $checked) {
							tryAddPublicationCoverage(
								$checkedUrls[$key]['company'],
								$publication['id'],
								$publication['name'],
								$gameId,
								"",
								$watchedGameId,
								"",
								$checkedUrls[$key]['title'],
								_fixrelativeurl($publication['url'], $checkedUrls[$key]['url']),
								$checkedUrls[$key]['time']
							);
						}
					}
					else {
						if (count($checkedUrls)) {
							echo "Checked urls:<br/>\n";
							print_r($checkedUrls);
						}
						else {
							echo "Could not find any additional urls matching game: <b>" . $name . "</b> / " . $name_safe . "<Br/>\n";
						}
					}
				};

				if ($checkLinks) {
					// print_r($checkedUrls);
					foreach ($games as $game) {
						/* $companyId,
						   $name,
						   $name_safe,
						   $gameId,
						   $gameCoverageRequireApproval,
						   $watchedGameId
						   */
						$checkForGameOrWatchedGame(
							$game['company'],
							$game['name'],
							strtolower(str_replace(" ", "-", $game['name'])),
							$game['id'],
							$game['coverageRequiresApproval'],
							0
						);
					}

					foreach ($watchedgames as $watchedgame) {
						$checkForGameOrWatchedGame(
							0,
							$watchedgame['name'],
							strtolower(str_replace(" ", "-", $watchedgame['name'])),
							$watchedgame['id'],
							0,
							0
						);
					}
				}

				preg_match('/<title>(.*)<\/title>/iU', $urlcontents, $titleMatches);
				// print_r($titleMatches);
				$title = "Unknown Page Title";
				if (count($titleMatches) > 1) {
					$title = "";
					for($i = 1; $i < count($titleMatches); $i++) {
						// echo $titleMatches[$i] . " vs " . $title . "<br/>\n";
						// echo strlen(trim($titleMatches[$i])) . " vs " . strlen($title) . "<br/>\n";
						if (strlen(trim($titleMatches[$i])) >= strlen($title)) {
							$title = mb_convert_encoding(trim($titleMatches[$i]), "UTF-8");
						}
					}
				}
				// 		$titleIndex++;
				// 		if ($titleIndex >= count($titleMatches)) {
				// 			$title = "Unknown Page Title";
				// 			break;
				// 		}
				// 	}
				// 	while (strlen($title) == 0);
				// }
				echo "Title: " . $title . "<br/>\n";
				// print_r($titleMatches);
				coverage_scrapeArticleContents($games, $watchedgames, $publication, $url, $title, time(), $urlcontents, $doAdd);

				return true;
			}
		}
	}
}


?>
