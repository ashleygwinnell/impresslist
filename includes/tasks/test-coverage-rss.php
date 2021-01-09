<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

util_publication_url_hash_purgeold();

//$publication = db_singlepublication($db, 198);
$publication = db_singlepublication($db, 6);
$urlcontents = url_get_contents($publication['rssfeedurl']);

//echo (util_publication_url_hash_exists("1"))?"true":"false";
//echo util_publication_url_hash_exists("12345")?"true":"false";
//die();
//$urlcontents = url_get_contents("http://fingerguns.net/");
if (strlen($urlcontents) == 0) {
	echo "Could not get contents of homepage. Skipping...<br/>\n";
} else {
	$doc = new DOMDocument();
	$doc->strictErrorChecking = false;
	@$doc->loadHTML( $urlcontents );
	$xml = simplexml_import_dom($doc);
	if ($xml == null) {
		echo "XML error - could not scrape page. Attempting regex scrape...<br/>\n";

		$derp = preg_match('/<a href="(.+)">/', $urlcontents, $match);
		$info = parse_url($match[1]);
		// echo $info['scheme'].'://'.$info['host']; // http://www.mydomain.com

	//	continue;
	}
	else
	{
		$game = db_singlegame($db, 8); // warborn

		$name = $game['name'];
		$name_safe = strtolower(str_replace(" ", "-", $game['name']));

		//$arr = $xml->xpath('//a[contains(concat(" ", @href, " "), "' . $name_safe . '")] | //a[contains(concat(" ", @title, " "), "' . $name . '")]');

		//foreach ($arr as $item) {
		//}
		//print_r($xml);
		//print_r($arr);

		$items = $xml->body->rss->channel->item;
		foreach($items as $item) {
			//print_r($item);

			$urlhash = util_publication_url_hash($publication['id'], $item->guid);
			$alreadyScraped = util_publication_url_hash_exists($urlhash);
			if ($alreadyScraped) {
				echo "already scraped " . $item->guid . "<br/>\n";
			} else {

				$articlecontents = url_get_contents($item->guid);

				if (strlen($articlecontents) == 0) {
					echo $item->guid . "  was empty. <br/>\n";
				} else {
					util_publication_url_hash_insert($urlhash);

					$containsGame = strpos(strtolower($articlecontents), strtolower($game['name'])) !== FALSE ||
									util_containsKeywords($articlecontents, $game['keywords'], false);
					if ($containsGame) {
						echo $item->guid . " contains " . $game['name'] . "<br/>\n";
					}
				}
			}

		}

	}
}


echo "<b>Done!</b>\n";


?>
