<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// id = 165 -- plus10damage.com

// Temp test
//$db->exec("UPDATE publication SET lastscrapedon = 0 WHERE id = 165;");
//$db->exec("UPDATE publication SET lastscrapedon = 0;");

// Publications
$publications = $db->query("SELECT * FROM publication WHERE lastscrapedon < " . (time()-3600) . " AND removed = 0;");
$num_publications = count($publications);

// Games
$games = $db->query("SELECT * FROM game;");
$num_games = count($games);

function fixrelativeurl($host, $url) {
	if (substr($url, 0, 1) == "/") { 
		if (substr($host, -1, 1) == "/") {
			return $host . substr($url, 1); 
		}
		return $host . $url; 
	}
	return $url;
}

function tryAddPublicationCoverage($publicationId, $publicationName, $gameId, $title, $url, $time) {
	global $db; 
	// YES! We got coverage.
	// ... but we need to make sure we don't have it saved already!
	echo "Found Coverage!<br/>\n";
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
		$stmt = $db->prepare("INSERT INTO publication_coverage (id, publication, person, game, url, title, `utime`) 
														VALUES (NULL, :publication, NULL, :game, :url, :title, :utime ); ");
		$stmt->bindValue(":publication", $publicationId, Database::VARTYPE_INTEGER); 
		$stmt->bindValue(":game", $gameId, Database::VARTYPE_INTEGER); 
		$stmt->bindValue(":url", $url, Database::VARTYPE_STRING); 
		$stmt->bindValue(":title", $title, Database::VARTYPE_STRING); 
		$stmt->bindValue(":utime", $time, Database::VARTYPE_INTEGER); 
		$stmt->execute();

		@email_new_coverage($publicationName, $url, $time);
		@slack_coverageAlert($publicationName, $title, $url);
	} else {
		echo $existingCoverage[0]['url'] . "<br/>\n";
		echo "<i>It was already in the database.</i><br/>\n";
	}
}


// for each publication
for($i = 0; $i < $num_publications; ++$i) {

	echo "<b>" . $publications[$i]['name'] . "</b>!<br/>\n";

	$doScrape = true;
	// Scrape RSS feed.
	$rss = $publications[$i]['rssfeedurl'];
	if (strlen($rss) > 0) 
	{
		echo "Checking RSS...<br/>\n";

		// Use XML parser on the feed.
		$rsscontent = url_get_contents($rss);
		$doc = new DOMDocument();
		$doc->strictErrorChecking = false;
		@$doc->loadHTML( $rsscontent );
		$xml = simplexml_import_dom($doc);
		$items = $xml->body->rss->channel->item;

		if ($items == null) {
			//print_r($xml);
			echo "Skipping...<br/>\n";
			//continue; 
		} else { 

			foreach ($items as $item) {
				$title = htmlentities($item->title);
				$time = strtotime($item->pubdate);
				$url = $item->link->__toString(); 
				if (strlen($url) == 0) {
					$url = $item->guid;
				}

				//print_r($item);
				//echo 

				foreach ($games as $game) {
					if (strpos($title, $game['name']) !== FALSE) {
						
						tryAddPublicationCoverage(
							$publications[$i]['id'], 
							$publications[$i]['name'], 
							$game['id'], 
							$title, 
							$url, 
							$time
						);

					}
				}
			}
			$doScrape = false;
			//echo $rsscontent;
			//continue;
		}
	}

	// Scrape homepage 
	if ($doScrape)
	{
		echo "Scraping homepage...<br/>\n";
		$url = $publications[$i]['url'];
		echo $url . "<br/>\n";

		$urlcontents = url_get_contents($url);
		if (strlen($urlcontents) == 0) {
			echo "Could not get contents of homepage. Skipping...<br/>\n";
			continue;
		}


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
			
			foreach ($games as $game) {
				$gamename = $game['name'];
				$gamename_safe = strtolower(str_replace(" ", "-", $game['name']));

				// match any links that contain the game name
				$arr = $xml->xpath('//a[contains(concat(" ", @href, " "), "' . $gamename_safe . '")] | //a[contains(concat(" ", @title, " "), "' . $gamename . '")]');
				
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

				//print_r($checkedUrls);

				// add to database!
				foreach ($checkedUrls as $key => $checked) {
					tryAddPublicationCoverage(
						$publications[$i]['id'], 
						$publications[$i]['name'], 
						$game['id'], 
						$checkedUrls[$key]['title'], 
						fixrelativeurl($publications[$i]['url'], $checkedUrls[$key]['url']), 
						$checkedUrls[$key]['time']
					);
				}
				
			}

		}


	}
	echo "<hr/>\n";

	// Update database lastscrapedon value.
	$db->exec("UPDATE publication SET lastscrapedon = " . time() . " WHERE id = " . $publications[$i]['id'] . " ;");
	sleep(1);
}

echo "<b>Done!</b>\n";

?>