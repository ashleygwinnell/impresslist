<?php

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");


die('no test running');






echo "<b>migrate</b><br/>";

$results = $db->query("SELECT * FROM game_key WHERE assignedByUser = 0 ORDER BY id;");
for($i = 0; $i < count($results); $i++) {
	$key = $results[$i]['keystring'];
	$emails = $db->query("SELECT * FROM email WHERE contents LIKE '%" . $key . "%'; ");
	if (count($emails) > 0) { 
		
		if ($results[$i]['assignedToType'] == 'person') {
			echo $emails[0]['id'] . " updated.<br/>";
			$db->exec("UPDATE game_key 
						SET 
							assignedByUser = '" . $emails[0]['user_id'] . "',  
							assignedByUserTimestamp = '" . $emails[0]['utime'] . "'  
						WHERE id = '" . $results[$i]['id']. "';");
		}

	} else {
		$people = $db->query("SELECT * FROM person WHERE id = '" . $results[$i]['assignedToTypeId'] . "'; ");
		echo "key " . $results[$i]['id'] . " / {$key} was not sent in an email. it was given to ".$people[0]['firstname']." ".$people[0]['surnames'].".<br/>";
	}
}

die();

$password = "";
$salt = "6irlxeqU.OXzmH2zudCiag==";
$encrypted = "639KS4ecCJbLGBzaE47aVoFq4m+KZpPWvLM3RZVAmsU=";
$decrypted = util_decrypt($encrypted, $salt);

echo "iv: " . $impresslist_encryption_iv . "<br/>";
echo "password: " . $password . "<br/>";
echo "salt: " . $salt . "<br/>";
echo "encrypted: " . $encrypted . "<br/>";
echo "decrypted: " . $decrypted . "<br/>";

//die();

$password = "hello";
$salt = util_getSalt($password);
$encrypted = util_encrypt($password, $salt);
$decrypted = util_decrypt($encrypted, $salt);

echo "<br/>";
echo "password: " . $password . "<br/>";
echo "salt: " . $salt . "<br/>";
echo "encrypted: " . $encrypted . "<br/>";
echo "decrypted: " . $decrypted . "<br/>";

die();

// HTML emails must include css styles inline on every tag.
// We tried looking at Premailer, and InlineStyle PHP relies on Symphony framework. 
// No single-file PHP solutions for this.

// Until then you can pipe your HTML page through services such as: 
// 	http://templates.mailchimp.com/resources/inline-css/
// 	https://inlinestyler.torchbox.com/

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

error_reporting(E_ALL);

//email_new_youtube_coverage("pewdiepie", "http://gamejolt.com", time());
//slack_coverageAlert("MoonsPod", "MvM #6: Friendship Club", "https://www.youtube.com/watch?v=-NsDGJlCyHs");

$people = $db->query("SELECT * FROM person WHERE 1 = 1;");
foreach($people as $person) {
	$id = $person['id'];
	$firstname = $person['firstname'];
	$surnames = $person['surnames'];

	/*$actualfirstname = substr($firstname, 0, strpos($firstname, " "));
	if ($actualfirstname == "") {
		$actualfirstname = $firstname;
	}

	$actualsurnames = substr($firstname, strpos($firstname, " "));

	echo $actualfirstname . " " . $actualsurnames . " --- " . $firstname . " " . $surnames . "<br/>";

	$stmt = $db->prepare("UPDATE person SET firstname = :firstname, surnames = :surnames WHERE id = :id; ");
	$stmt->bindValue(":id", $id, Database::VARTYPE_INTEGER);
	$stmt->bindValue(":firstname", $actualfirstname, Database::VARTYPE_STRING);
	$stmt->bindValue(":surnames", $actualsurnames, Database::VARTYPE_STRING);
	$rs = $stmt->execute();*/

	if (substr($surnames, 0, 1) == " ") {
		$newsurname = trim($surnames);
		$stmt = $db->prepare("UPDATE person SET surnames = :surnames WHERE id = :id; ");
		$stmt->bindValue(":id", $id, Database::VARTYPE_INTEGER);
		$stmt->bindValue(":surnames", $newsurname, Database::VARTYPE_STRING);
		$rs = $stmt->execute();
	}
}

?>