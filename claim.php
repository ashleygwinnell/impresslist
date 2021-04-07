<?php

//header("Location: /");
//die();

$require_config = true;
$require_login = false;

include_once("includes/checks.php");
include_once('init.php');

$game_p = $_GET['game'];
$platform = $_GET['platform'];

if (!isset($_GET['game']) || !isset($_GET['platform'])) {
	include_once("includes/404.html");
	die();
}

$game = db_singlegame($db,$game_p);
if (!$game) {
	include_once("includes/404.html");
	die();
}

if (!util_isValidPlatformForProjectKeys($platform)) {
	include_once("includes/404.html");
	die();
}

$error = false;
$errorMessage = "";

$key = db_singleavailablekeyforgame($db, $game_p, $platform, $subplatform);
if (!$key) {
	$error = true;
	$errorMessage = "No keys are remaining. :(";
}

$validRegions = util_listNintendoRegions();

if (isset($_POST) && count($_POST) > 0) {

	if ($platform == "switch") {
		// validate post details
		if (!isset($_POST['email']) || !isset($_POST['region'])) {
			$error = true;
			$errorMessage = "Email or region was not set.";
		} else {
			$email = $_POST['email'];
			$region = $_POST['region'];

			if (!in_array($region, array_keys($validRegions))) {
				$error = true;
				$errorMessage = "Invalid region set";
			}
			else if (strlen(trim($email)) == 0 || !util_isEmail($email)) {
				$error = true;
				$errorMessage = "Invalid email address.";
			}
			else {
				//$people = $db->query("SELECT * from person WHERE email = :email LIMIT 1;");

				$stmt = $db->prepare("SELECT id FROM person where email = :email LIMIT 1;");
				$stmt->bindValue(":email", $email, Database::VARTYPE_STRING);
				$people = $stmt->query();

				$stmt = $db->prepare("SELECT id FROM publication where email = :email LIMIT 1;");
				$stmt->bindValue(":email", $email, Database::VARTYPE_STRING);
				$publications = $stmt->query();

				$stmt = $db->prepare("SELECT id FROM youtuber where email = :email LIMIT 1;");
				$stmt->bindValue(":email", $email, Database::VARTYPE_STRING);
				$youtubers = $stmt->query();

				$total = count($people) + count($youtubers) + count($publications) + count($twitchStreamers);
				if ($total == 0) {
					$error = true;
					$errorMessage = "Invalid email address.";
				} else {

					// Check key/s are not already assigned.

					// Send email

					$success = true;
					// TODO:
				}



			}

		}
	} else {
		$error = true;
		$errorMessage = "Invalid platform.";
	}
}

include_once("includes/claim.html");

?>
