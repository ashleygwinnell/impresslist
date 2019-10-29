<?php

ini_set("allow_url_fopen", "On");
set_time_limit(0);

$testMode = false;

$max_publications = 50;
$max_url_scrapes_per_publication = 50;
if ($testMode) {
	$max_publications = 1;
	$max_url_scrapes_per_publication = 50;
}


$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/includes/checks.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");
//die($_SERVER['DOCUMENT_ROOT']);

// Temp test
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 228;");//nintendo times
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 158;");//onelifeleft
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 29;"); // appadvice
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 201;"); // gonintendo
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 207;"); // nintendo everything
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 89;");//game people - check pub date
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 202;");//nintendoworldreport
// $db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 200;");//nintendonl
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 149;");//tech raptor
//$db->exec("UPDATE publication SET lastscrapedon = 0;");

util_publication_url_hash_purgeold();

// error_reporting(E_ALL ^ E_NOTICE);
// use Readability\Readability;
// $readabilitytest = new Readability("", "");
// die("no");

//$db->exec("DELETE FROM publication_coverage WHERE utime > " . (time()-(86400*3)) . ";");
// $db->exec("DELETE FROM cache_external_urlbools WHERE 1 = 1;");
// $db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 66;");
//die();

// Publications
$publications = $db->query("SELECT * FROM publication WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0 ORDER BY lastscrapedon ASC;");
$num_publications = count($publications);

// Games
$games = $db->query("SELECT * FROM game WHERE removed = 0;");
$num_games = count($games);

// Watched Games
$watchedgames = $db->query("SELECT * FROM watchedgame WHERE removed = 0;");
$num_watchedgames = count($watchedgames);



function fixrelativeurl($host, $url) {
	if (substr($url, 0, 1) == "/") {
		if (substr($host, -1, 1) == "/") {
			return $host . substr($url, 1);
		}
		return $host . $url;
	}
	return $url;
}

function tryAddPublicationCoverage($companyId, $publicationId, $publicationName, $gameId, $gameName, $watchedGameId, $watchedGameName, $title, $url, $time) {
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
	echo "Found Coverage for <b>{$gameName} {$watchedGameName}</b>!<br/>\n";
	$stmt = $db->prepare("SELECT * FROM publication_coverage WHERE url = :url; ");
	$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
	$existingCoverage = $stmt->query();
	if (count($existingCoverage) == 0) {

		echo "Adding coverage from {$publicationName}<br/>\n";
		echo $title . "<br/>\n";
		echo $url . "<br/>\n";
		echo $time . "<br/>\n";
		echo "<hr/>\n";
		// Add it to the database.
		$stmt = $db->prepare("INSERT INTO publication_coverage (id, publication, person, game, watchedgame, url, title, `utime`)
														VALUES (NULL, :publication, NULL, :game, :watchedgame, :url, :title, :utime ); ");
		$stmt->bindValue(":publication", $publicationId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":watchedgame", $watchedGameId, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING);
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING);
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER);
		$stmt->execute();

		if ($watchedGameId == null && $companyId != 0) {
			@email_new_coverage($companyId, $publicationName, $url, $time);
			@slack_coverageAlert($companyId, $publicationName, $title, $url);
			@discord_coverageAlert($companyId, $publicationName, $title, $url);
		}
	} else {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database.</i><br/>\n";
	}
}


// for each publication
for($i = 0; $i < $num_publications && $i < $max_publications; $i++) {

	echo "<b>" . $publications[$i]['name'] . "</b>!<br/>\n";


	$doScrape = true;
	// Scrape RSS feed.
	$rss = $publications[$i]['rssfeedurl'];
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
			//continue;
		} else if (!is_object($xml)) {
			echo "Invalid XML for website. Did not make XML object.<br/>\n";
			//continue;
		} else {
			$items = $xml->channel->item;
			//$items = $xml->body->rss->channel->item;
			//print_r($xml);

			if ($items == null) {
				//print_r($xml);
				echo "Skipping...<br/>\n";
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
					//echo

					// Scan titles and descriptions
					foreach ($games as $game)
					{
						$titleContainsGame = strpos(strtolower($title), strtolower($game['name'])) !== FALSE || util_containsKeywords($title, $game['keywords']);
						$descriptionContainsGame = strpos(strtolower($description), strtolower($game['name'])) !== FALSE || util_containsKeywords($description, $game['keywords']);
						$articleContainsBlackwords = util_containsKeywords($title, $game['blackwords']) || util_containsKeywords($description, $game['blackwords']);

						if ($titleContainsGame || $descriptionContainsGame) {
							if (!$articleContainsBlackwords) {
								tryAddPublicationCoverage($game['company'], $publications[$i]['id'], $publications[$i]['name'], $game['id'], $game['name'], null, "", $title, $url, $time );
							} else {
								echo "Found Coverage for <b>" . $game['name'] . "</b> (" . $game['id'] . ") ({$url}) - but it contained a blackword!<br/>\n";
							}
						}
					}
					foreach($watchedgames as $watchedgame) {
						$titleContainsGame = strpos(strtolower($title), strtolower($watchedgame['name'])) !== FALSE || util_containsKeywords($title, $watchedgame['keywords']);
						$descriptionContainsGame = strpos(strtolower($description), strtolower($watchedgame['name'])) !== FALSE || util_containsKeywords($description, $watchedgame['keywords']);
						$articleContainsBlackwords = util_containsKeywords($title, $watchedgame['blackwords']) || util_containsKeywords($description, $watchedgame['blackwords']);

						if ($titleContainsGame || $descriptionContainsGame) {
							if (!$articleContainsBlackwords) {
								tryAddPublicationCoverage(0, $publications[$i]['id'], $publications[$i]['name'], null, "", $watchedgame['id'], $watchedgame['name'], $title, $url, $time );
							} else {
								echo "Found Coverage for watched game <b>" . $watchedgame['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
							}
						}
					}

					// Scan each rss article contents!
					$urlhash = util_publication_url_hash($publications[$i]['id'], $url);
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

							$articlecontents_lc = strtolower($articlecontents);
							$articlecontents_lc = util_cleanHtmlArticleContents($url, $articlecontents_lc); //util_cleanhtml($articlecontents_lc);
							if (strlen($articlecontents_lc) == 0) {
								echo "cleaned content was empty... ({$url}) <br/>\n";
							}
							else {
								foreach ($games as $game) {
									$contains = util_muddyCoverageContains($articlecontents_lc, $game['name'], $game['keywords']);
									$containsBlackwords = util_containsKeywords($articlecontents_lc, $game['blackwords']);

									if ($contains) {
										if (!$containsBlackwords) {
											tryAddPublicationCoverage($game['company'], $publications[$i]['id'], $publications[$i]['name'], $game['id'], $game['name'], null, "", $title, $url, $time );
										} else {
											echo "Found Coverage for <b>" . $game['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
										}
									}
								}
								foreach($watchedgames as $watchedgame) {

									$contains = util_muddyCoverageContains($articlecontents_lc, $watchedgame['name'], $watchedgame['keywords']);
									$containsBlackwords = util_containsKeywords($articlecontents_lc, $watchedgame['blackwords']);

									if ($contains) {
										if (!$containsBlackwords) {
											tryAddPublicationCoverage(0, $publications[$i]['id'], $publications[$i]['name'], null, "", $watchedgame['id'], $watchedgame['name'], $title, $url, $time );
										} else {
											echo "Found Coverage for watched game <b>" . $watchedgame['name'] . "</b> ({$url}) - but it contained a blackword!<br/>\n";
										}


										//print_r($articlecontents_lc);
										//die();
									}
								}

								util_publication_url_hash_insert($urlhash);
							}
						}
						if ($countUrlScrapes >= $max_url_scrapes_per_publication) {
							break;
						}
					}


				}
				$doScrape = false;
				//echo $rsscontent;
				//continue;
			}
		}
	}

	//die($_SERVER['DOCUMENT_ROOT']);

	// Scrape homepage
	if ($doScrape)
	{
		echo "Scraping homepage...<br/>\n";
		$url = $publications[$i]['url'];
		echo $url . "<br/>\n";

		//die("2" . $_SERVER['DOCUMENT_ROOT']);
		if (strlen(trim($url)) > 0) {

			$urlcontents = url_get_contents($url);
			if (strlen($urlcontents) == 0) {
				echo "Could not get contents of homepage. Skipping...<br/>\n";
				//die();
				//continue;
			} else {

				//die('ha');

				$doc = new DOMDocument();
				$doc->strictErrorChecking = false;
				@$doc->loadHTML( $urlcontents );
				$xml = simplexml_import_dom($doc);
				if ($xml == null) {
					echo "XML error - could not scrape page. Attempting regex scrape...<br/>\n";

					$derp = preg_match('/<a href="(.+)">/', $urlcontents, $match);
					$info = parse_url($match[1]);
					echo $info['scheme'].'://'.$info['host']; // http://www.mydomain.com

				//	continue;
				}
				else
				{

					$checkedUrls = array();
					$checkForGameOrWatchedGame = function($companyId, $name, $name_safe, $gameId, $watchedGameId) use ($xml, $checkedUrls) {

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
								$obj['url'] = fixrelativeurl($url, $href->__toString());
								$obj['time'] = time();
								if (isset($__attrs['title'])) {
									$obj['title'] = htmlentities($__attrs['title']->__toString());
								}
								$obj['company'] = $company;
								$checkedUrls[] = $obj;
							} else {
								for($j = 0; $j < count($checkedUrls); $j++) {
									if ($checkedUrls[$j]['url'] == fixrelativeurl($url, $href->__toString())) {
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
								$url2 = fixrelativeurl($url, $checkedUrls[$key]['url']);
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
								} //else {

									$checkedUrls[$key]['title'] = htmlentities($xml2->head->title->__toString());

								//	}
							}
						}
					};

						//print_r($checkedUrls);

					foreach ($games as $game) {
						$checkForGameOrWatchedGame(
							$game['company'],
							$game['name'],
							strtolower(str_replace(" ", "-", $game['name'])),
							$game['id'],
							0
						);
					}

					foreach ($watchedgames as $watchedgame) {
						$checkForGameOrWatchedGame(
							0,
							$watchedgame['name'],
							strtolower(str_replace(" ", "-", $watchedgame['name'])),
							$watchedgame['id'],
							0
						);
					}

					// TODO: how the heck is this working?
					// add to database!
					foreach ($checkedUrls as $key => $checked) {
						tryAddPublicationCoverage(
							$checkedUrls[$key]['company'],
							$publications[$i]['id'],
							$publications[$i]['name'],
							$gameId,
							"",
							$watchedGameId,
							"",
							$checkedUrls[$key]['title'],
							fixrelativeurl($publications[$i]['url'], $checkedUrls[$key]['url']),
							$checkedUrls[$key]['time']
						);
					}

				}
			}
		}

	}
	echo "<hr/>\n";

	//die('3. blah');

	// Update database lastscrapedon value.
	$db->exec("UPDATE publication SET lastscrapedon = " . time() . " WHERE id = " . $publications[$i]['id'] . " ;");
	//sleep(1);
}

echo "<b>Done!</b>\n";

?>
