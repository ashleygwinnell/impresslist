<?php

set_time_limit(0);

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");
error_reporting(E_ALL);

function emailhash($result) {
	return $result['user_id'] . '-' . $result['utime'] . '-' . $result['from_email'] . '-' . $result['to_email'] . '-' . $result['subject'];
}

$results = $db->query("SELECT id, user_id, utime, from_email, to_email, subject FROM email ORDER BY id ASC LIMIT 10000;");

$uniques = array();
$dupes = array();
for($i = 0; $i < count($results); $i++) {
	$hash = emailhash($results[$i]);
	if (in_array($hash, $uniques)) {
		$dupes[] = $results[$i]['id'];
		//echo $results[$i]['id'] . " :: " . $hash . " IS A DUPE<br/>";
	}
	$uniques[] = $hash;
}
//print_r($dupes);
echo count($dupes) . ' duplicates<br/>';
//echo "hey";

// for($i = 0; $i < count($dupes); $i++) {
// 	$one_dupe = $db->query("SELECT * FROM email WHERE id = '" . $dupes[0] . "' LIMIT 1;")[0];

// 	$would_be_dupe = util_wouldEmailBeDuplicate($one_dupe['user_id'], $one_dupe['utime'], $one_dupe['from_email'], $one_dupe['to_email'], $one_dupe['subject'], $one_dupe['contents']);
// 	echo "would 0 be dupe? " . $would_be_dupe;
// 	echo "<br/>";

// 	if ($would_be_dupe) {
// 		$db->query("DELETE FROM email WHERE id = " . $dupes . " LIMIT 1;");
// 	}
// }

$db->query("DELETE FROM email WHERE id in (" . implode(",", $dupes) . ");");

// echo "deleted " . count($dupes) . " duplicates";
//echo "<br/>";

?>
