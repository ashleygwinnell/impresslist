<?php

set_time_limit(0);
ini_set("allow_url_fopen", "On");

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Publications
$publications = $db->query("SELECT * FROM publication WHERE lastpostedon_updatedon < " . (time()-3600) . " AND removed = 0;");
$num_publications = count($publications);

for($i = 0; $i < $num_publications; ++$i) {
	$rss = $publications[$i]['rssfeedurl'];
	if (strlen($rss) > 0) {
		//echo $rss . "<Br/>";
		$rsscontent = url_get_contents($rss);
		$latestArticleTimestamp = 0;
		$offset = 0;
		while(true) {
			$resStart = strpos($rsscontent, "<pubDate>", $offset);
			if ($resStart === FALSE) { break; }
			$resStart += 9;
			$resEnd = strpos($rsscontent, "</pubDate>", $resStart);
			$thisDate = strtotime( substr($rsscontent, $resStart, $resEnd - $resStart) );
			//echo $thisDate . "<br/>";
			$offset = $resEnd;

			if ($thisDate > $latestArticleTimestamp) {
				$latestArticleTimestamp = $thisDate;
			}
		}

		if ($latestArticleTimestamp == 0) {
			$offset = 0;
			while(true) {
				$resStart = strpos($rsscontent, "<updated>", $offset);
				if ($resStart === FALSE) { break; }
				$resStart += 9;
				$resEnd = strpos($rsscontent, "</updated>", $resStart);
				$thisDate = strtotime( substr($rsscontent, $resStart, $resEnd - $resStart) );
				//echo $thisDate . "<br/>";
				$offset = $resEnd;

				if ($thisDate > $latestArticleTimestamp) {
					$latestArticleTimestamp = $thisDate;
				}
			}
		}



		if ($latestArticleTimestamp > 0) {
			echo "<b>" . $publications[$i]['name'] . "</b><br/>";
			echo $latestArticleTimestamp . "<br/>";
			echo "<hr/>";

			$stmt = $db->prepare(" UPDATE publication
									SET
										lastpostedon = :lastpostedon,
										lastpostedon_updatedon = :lastpostedon_updatedon
									WHERE id = :id;");
			$stmt->bindValue(":id", $publications[$i]['id'], Database::VARTYPE_INTEGER);
			$stmt->bindValue(":lastpostedon", $latestArticleTimestamp, Database::VARTYPE_INTEGER);
			$stmt->bindValue(":lastpostedon_updatedon", time(), Database::VARTYPE_INTEGER);
			$stmt->execute();
			//$stmt->close();

			sleep(1);
		}
	}
}

?>