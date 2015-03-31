<?php

set_time_limit(0);
ini_set("allow_url_fopen", "On");

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Publications
$publications = array();
$publications_resultset = $db->query("SELECT * FROM publication WHERE removed = 0;");
while($row = $publications_resultset->fetchArray(SQLITE3_ASSOC)) { $publications[] = $row; }
$num_publications = count($publications);


for($i = 0; $i < $num_publications; ++$i) {
	$rss = $publications[$i]['rssfeedurl'];
	if (strlen($rss) > 0) {
		//echo $rss . "<Br/>";
		echo "<b>" . $publications[$i]['name'] . "</b><br/>";
		$rsscontent = file_get_contents($rss);
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

		echo $latestArticleTimestamp . "<br/>";
		echo "<hr/>";

		if ($latestArticleTimestamp > 0) {
			$stmt = $db->prepare(" UPDATE publication SET lastpostedon = :lastpostedon WHERE id = :id;");
			$stmt->bindValue(":id", $publications[$i]['id'], SQLITE3_INTEGER);
			$stmt->bindValue(":lastpostedon", $latestArticleTimestamp, SQLITE3_INTEGER);
			$stmt->execute();
			$stmt->close();
		}
	}
}

?>