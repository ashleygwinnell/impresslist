<?php

set_time_limit(0);
ini_set("allow_url_fopen", "On");

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Publications
$publications = $db->query("SELECT * FROM publication WHERE iconurl_updatedon < " . (time()-3600) . " AND removed = 0; ");
$num_publications = count($publications);

echo "Updating favicons...<br/>";
echo "<hr/>";

function skip($name) {
	echo "<b>" . $name . "</b><br/>";
	echo "skipped!<br/>";
	echo "<hr/>";
}

for($i = 0; $i < $num_publications; ++$i) {
	$url = $publications[$i]['url'];
	if (strlen($url) > 0) {

		$rsscontent = url_get_contents($url);
		
		//$resStart = strpos($rsscontent, "<link rel='icon'", 0);
		//if ($resStart === FALSE) { continue; }

		// Option 1
		$doc = new DOMDocument();
		$doc->strictErrorChecking = false;
		@$doc->loadHTML( $rsscontent );
		$xml = @simplexml_import_dom($doc);
		if ($xml === FALSE) { skip($publications[$i]['name']); continue; }
		else if (!is_object($xml)) { skip($publications[$i]['name']); continue; }

		$arr = $xml->xpath('//link[@rel="shortcut icon"]');
		if ($arr === FALSE) { skip($publications[$i]['name']); continue; }

		$faviconUrl = $arr[0]['href'];
		if (strlen($faviconUrl) == 0) { 
			// Option 2
			$faviconUrl = "http://www.google.com/s2/favicons?domain=" . $url;
		} else if (substr($faviconUrl, 0, 1) == "/") { 
			if (substr($url, -1, 1) == "/") {
				$faviconUrl = $url . substr($faviconUrl, 1); 
			} else {
				$faviconUrl = $url . $faviconUrl;	
			}
		} else if (substr($faviconUrl, 0, 7) != "http://" && substr($faviconUrl, 0, 8) != "https://") {
			if (substr($url, -1, 1) == "/") {
				$faviconUrl = $url . $faviconUrl;
			} else {
				$faviconUrl = $url . "/" . $faviconUrl;	
			}
		}

		//if (strlen($faviconUrl) > 0) {
			echo "<b>" . $publications[$i]['name'] . "</b><br/>";
			echo $faviconUrl . "<br/>";
			echo "<hr/>";

			$stmt = $db->prepare(" UPDATE publication SET iconurl = :iconurl, iconurl_updatedon = :iconurl_updatedon WHERE id = :id;");
			$stmt->bindValue(":id", $publications[$i]['id'], Database::VARTYPE_INTEGER);
			$stmt->bindValue(":iconurl", $faviconUrl, Database::VARTYPE_STRING);
			$stmt->bindValue(":iconurl_updatedon", time(), Database::VARTYPE_INTEGER);
			$stmt->execute();

			sleep(1);
		//}
	}
}

?>