<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

$queue = $db->query("SELECT * FROM emailcampaignsimple WHERE ready = 1 AND sent = 0 AND removed = 0 AND `timestamp` <= " . time() . " ORDER BY `timestamp` LIMIT 10;");
//print_r($queue);

$Parsedown = new Parsedown();

$games = $db->query("SELECT * FROM game WHERE removed = 0;");

for ($i = 0; $i < count($queue); $i++)
{
	$campaign = $queue[$i];
	$recipients = json_decode($campaign['recipients'], true);
	$user = db_singleuser($db, $campaign['user'], ['emailIMAPPassword', 'emailIMAPPasswordSalt', 'emailIMAPPasswordIV']);

	util_setIV($user['emailIMAPPasswordIV']);
	$userPassword = util_decrypt($user['emailIMAPPassword'], $user['emailIMAPPasswordSalt']);

	/*

	This doesn't appear to work on our live server. Take it out and use phpmailer.

	imap_timeout(IMAP_OPENTIMEOUT, 5);
	imap_timeout(IMAP_READTIMEOUT, 5);
	imap_timeout(IMAP_WRITETIMEOUT, 5);
	imap_timeout(IMAP_CLOSETIMEOUT, 5);
	$imapServer = "{" . $user['emailIMAPServer'] . ":993/ssl/novalidate-cert}INBOX";
	$imap_connection = imap_open($imapServer, $user['email'], $userPassword);

	print_r( imap_errors() );

	die('hahaha');

	*/


$headers = "MIME-Version: 1.0
Content-type: text/html; charset=iso-8859-1
From: " . $user['forename'] . " " . $user['surname'] . " <" . $user['email'] . ">
Reply-To: " . $user['email'] . "
X-Mailer: impresslist/" . $impresslist_version;

	//print_r($campaign);
	//print_r($recipients);

	$done_emails = [];
	// TODO: check duplicates.

	$all_sent = true;
	for($j = 0; $j < count($recipients); $j++)
	{
		if ($recipients[$j]['sent'] == 0)
		{
			$all_sent = false;
			$person = null;
			$recipient_email = "";
			$use_firstname = "";
			$use_surnames = "";
			$use_country = "";
			if ($recipients[$j]['type'] == "person")
			{
				$person = db_singleperson($db, $recipients[$j]['person_id']);
				$recipient_email = $person['email'];
				$use_firstname = $person['firstname'];
				$use_surnames = $person['surnames'];
				$use_country = $person['country'];
			}
			else if ($recipients[$j]['type'] == "personPublication")
			{
				$perpub = db_singlepersonpublication($db, $recipients[$j]['personPublication_id']);
				$person = db_singleperson($db, $perpub['person']);
				$recipient_email = $perpub['email'];
				$use_firstname = $person['firstname'];
				$use_surnames = $person['surnames'];
				$use_country = $person['country'];
			}
			else if ($recipients[$j]['type'] == "publication")
			{
				$pub = db_singlepublication($db, $recipients[$j]['publication_id']);
				$recipient_email = $pub['email'];
				$use_firstname = $pub['name'];
				$use_surnames = "";
				$use_country = $pub['country'];
			}
			else if ($recipients[$j]['type'] == "youtuber")
			{
				$youtuber = db_singleyoutubechannel($db, $recipients[$j]['youtuber_id']);
				$recipient_email = $youtuber['email'];
				$use_firstname = $youtuber['name'];
				if (strlen($use_firstname) == 0 && strlen($youtuber['name_override']) > 0) {
					$use_firstname = $youtuber['name_override'];
				}
				$use_surnames = "";
				$use_country = $youtuber['country'];
			}
			else if ($recipients[$j]['type'] == "twitchchannel")
			{
				$twitchchannel = db_singletwitchchannel($db, $recipients[$j]['twitchchannel_id']);
				$recipient_email = $twitchchannel['email'];
				$use_firstname = $twitchchannel['name'];
				if (strlen($use_firstname) == 0 && strlen($twitchchannel['twitchUsername']) > 0) {
					$use_firstname = $twitchchannel['twitchUsername'];
				}
				$use_surnames = "";
				$use_country = ""; // error in all twitch mailouts.
			}
			else {
				echo "Skipping e-mail line: " . json_encode($recipients[$j]);
				continue;
			}
			$recipient_type = $recipients[$j]['type'];
			$recipient_typeId = $recipients[$j][$recipients[$j]['type'].'_id'];

			echo "<hr/>";
			echo "Sending e-mail <i>" . $campaign['subject'] . "</i> to " . $use_firstname . " " . $use_surnames . " (" . $recipient_email . "). <br/>\n";

			// templates
			$markdown = $campaign['markdown'];
			$markdown = str_replace("{{first_name}}", $use_firstname, $markdown);

			// Keys tagged
			$assignsSingleKeys = [
				"steam" => false,
				"switch" => false
			];
			$assignsSingleKeys_id = [
				"steam" => 0,
				"switch" => 0
			];
			$assignsSingleKeys_code = [
				"steam" => '',
				"switch" => ''
			];

			$assign_platform_keys_if_tagged = function($gameId, $gameName, $gameTagSuffix, $platform, $subplatform, $platformName) use ($db,
																						&$user,
																						&$markdown,
																						&$assignsSingleKeys_id,
																						&$assignsSingleKeys_code,
																						&$assignsSingleKeys,
																						$recipient_type,
																						$recipient_typeId) {

				$gamenamesuffix = (strlen($gameTagSuffix) > 0)
					? " (" . $gameName . ")"
					: "";

				$assignsSingleKeys[$platform] = (strpos($markdown, "{{".$platform."_key" . $gameTagSuffix . "}}") !== FALSE);
				if ($assignsSingleKeys[$platform]) {
					echo "Replacing {{".$platform."_key" . $gameTagSuffix . "}} in email.<br/>\n";
					$availableKey = db_singleavailablekeyforgame($db, $gameId, $platform, $subplatform);
					$assignsSingleKeys_id[$platform] = $availableKey['id'];
					$assignsSingleKeys_code[$platform] = $availableKey['keystring'];
					$markdown = str_replace("{{".$platform."_key" . $gameTagSuffix . "}}", $assignsSingleKeys_code[$platform], $markdown);
				}
				else if (strpos($markdown, "{{".$platform."_keys" . $gameTagSuffix . "}}") !== false) {
					echo "Replacing {{".$platform."_keys" . $gameTagSuffix . "}} (plural) in email.<br/>\n";
					$keysForContact = db_keysassignedtotype($db, $gameId, $platform, $subplatform, $recipient_type, $recipient_typeId);
					//print_r($keysForContact);

					if (count($keysForContact) == 0) {
						echo "Assigning new key<br/>\n";
						$availableKey = db_singleavailablekeyforgame($db, $gameId, $platform, $subplatform);
						//echo "key: " . $availableKey;
						print_r($availableKey);

						$assignsSingleKeys[$platform] = true;
						$assignsSingleKeys_id[$platform] = $availableKey['id'];
						$assignsSingleKeys_code[$platform] = $availableKey['keystring'];

						$keys_md = "**".$platformName." Key" . $gamenamesuffix . ":**\n\n";
						$keys_md .= "* " . $assignsSingleKeys_code[$platform]. "\n\n";
						$markdown = str_replace("{{".$platform."_keys" . $gameTagSuffix . "}}", $keys_md, $markdown);
					} else {
						$plural = count($keysForContact) >= 2;
						$keys_md = "**".$platformName." Key" . (($plural)?"s":"") . "" . $gamenamesuffix . ":**\n\n";
						for($k = 0; $k < count($keysForContact); $k++) {
							$datetimestring = date("jS F Y", $keysForContact[$k]['assignedByUserTimestamp']);
							$keys_md .= "* " . $keysForContact[$k]['keystring'] . " *(Sent on " . $datetimestring . ")*\n";
						}
						$keys_md .= "\n";

						$markdown = str_replace("{{".$platform."_keys" . $gameTagSuffix . "}}", $keys_md, $markdown);
					}
				}

			};

			$switchCodeSubplatform = util_findNintendoRegionForCountry($use_country);
			if ($switchCodeSubplatform == '') {
				echo "Invalid country/region for " . $recipients[$j]['type'] . ": " .$use_firstname;
				die();
			}

			$assign_platform_keys_if_tagged($user['currentGame'], "", "", "steam", "", "Steam");
			$assign_platform_keys_if_tagged($user['currentGame'], "", "", "switch", $switchCodeSubplatform, "Nintendo Switch");

			for($k = 0; $k < count($games); $k++) {
				$thisgame_id = $games[$k]['id'];
				$thisgame_suffix = ":" . $games[$k]['nameuniq'];
				$thisgame_name = $games[$k]['name'];
				$assign_platform_keys_if_tagged($thisgame_id, $thisgame_name, $thisgame_suffix,  "steam", "", "Steam");
				$assign_platform_keys_if_tagged($thisgame_id, $thisgame_name, $thisgame_suffix, "switch", $switchCodeSubplatform, "Nintendo Switch");
			}


			$html_contents = $Parsedown->text($markdown);

			$urlroot = (isset($_SERVER['HTTPS'])?"https://":"http://") . $_SERVER['HTTP_HOST'];
			$html_message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
								<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
										<meta http-equiv="Content-Language" content="en-us">
									</head>
									<body>' . $html_contents . '<img src="' . $urlroot . '/pixel.php?type=simple-mailout&id=' . $campaign['id'] . '&recipient=' . $recipient_email . '"/></body>
								</html>';

			// Try to send the email with the user's IMAP connection,
			// otherwise revert to standard ol' webmail.

			$mail = new PHPMailer(); // create a new object
			$mail->IsSMTP(); // enable SMTP
			$mail->Host = $user['emailSMTPServer'];//  "smtp.gmail.com";
			$mail->Port = 587; // 465 or 587
			$mail->Timeout  =  5;
			$mail->SMTPDebug = 0; // debugging: 1 = errors and messages, 2 = messages only
			$mail->SMTPAuth = true; // authentication enabled
			$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail (or tls)
			$mail->Username = $user['email'];
			$mail->Password = $userPassword;

			$mail->setFrom($user['email'], $user['forename'] . " " . $user['surname']);
			$mail->addAddress($recipient_email, $use_firstname . " " . $use_surnames);
			//$mail->addBCC( $impresslist_emailAddress ); // We add this manually.

			$mail->IsHTML(true);

			$mail->XMailer = "impresslist/{$impresslist_version}";
			$mail->Subject = $campaign['subject'];
			$mail->Body = $html_message;
			$mail->AltBody = $markdown;

			if(!$mail->Send())
			{
				echo "Mailer Error: " . $mail->ErrorInfo;
				if (util_isLocalhost()) {
					echo "Localhost cannot send e-mail?";
				}
				die();
			}
			else
			{
				echo "Message has been sent.<br/>";
				$recipients[$j]['sent'] = true;

				$stmt = $db->prepare(" 	UPDATE emailcampaignsimple
										SET
											recipients = :recipients
										WHERE id = :id;");
				$stmt->bindValue(":recipients", json_encode($recipients), Database::VARTYPE_STRING);
				$stmt->bindValue(":id", $campaign['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				echo "hey";echo "<br/>";echo "<br/>";

				print_r($user);
				echo "<br/>";echo "<br/>";

				print_r($person);
				echo "<br/>";echo "<br/>";

				print_r($assignsSingleKeys);
				echo "<br/>";echo "<br/>";
				print_r($assignsSingleKeys_id);
				echo "<br/>";echo "<br/>";
				print_r($assignsSingleKeys_code);
				echo "<br/>";echo "<br/>";

				// Add this email to the general list of e-mails between people.
				$stmt = $db->prepare("INSERT INTO email (id, 	user_id, 	person_id, 	publication_id, youtuber_id, game_id, utime, 	from_email,  to_email, 	subject,  contents, unmatchedrecipient, removed   )
													VALUES  (NULL, :user_id, 	:person_id, :publication_id, :youtuber_id, :game_id, :utime, :from_email, :to_email, :subject, :contents, :unmatchedrecipient, 0 );");
				$stmt->bindValue(":user_id", $user['id'], Database::VARTYPE_INTEGER);

				if ($recipients[$j]['type'] == "person") {
					$stmt->bindValue(":person_id", $person['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", 0, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":youtuber_id", 0, Database::VARTYPE_INTEGER);
				}
				else if ($recipients[$j]['type'] == "personPublication") {
					$stmt->bindValue(":person_id", $person['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", $perpub['publication'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":youtuber_id", 0, Database::VARTYPE_INTEGER);
				}
				else if ($recipients[$j]['type'] == "publication") {
					$stmt->bindValue(":person_id", 0, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", $pub['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":youtuber_id", 0, Database::VARTYPE_INTEGER);
				}
				else if ($recipients[$j]['type'] == "youtuber") {
					$stmt->bindValue(":person_id", 0, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", 0, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":youtuber_id", $youtuber['id'], Database::VARTYPE_INTEGER);
				}
				$stmt->bindValue(":game_id", $user['currentGame'], Database::VARTYPE_INTEGER);

				$stmt->bindValue(":utime", time(), Database::VARTYPE_INTEGER);
				$stmt->bindValue(":from_email", $user['email'], Database::VARTYPE_STRING);
				$stmt->bindValue(":to_email", $recipient_email, Database::VARTYPE_STRING);
				$stmt->bindValue(":subject", $campaign['subject'], Database::VARTYPE_STRING);
				$stmt->bindValue(":contents", $html_message, Database::VARTYPE_STRING);
				$stmt->bindValue(":unmatchedrecipient", 0, Database::VARTYPE_INTEGER);
				$rs = $stmt->execute();
				if (!$rs) {
					// ...
				}

				if ($recipients[$j]['type'] == "person" || $recipients[$j]['type'] == "personPublication") {
					$stmt = $db->prepare("UPDATE person SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
					$stmt->bindValue(":id", $person['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontacted", time(), Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", $user['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();
				}
				if ($recipients[$j]['type'] == "personPublication") {
					$stmt = $db->prepare("UPDATE person_publication SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE person = :person_id AND publication = :publication_id ");
					$stmt->bindValue(":person_id", $person['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", $perpub['publication'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontacted", time(), Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", $user['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();
				}
				else if ($recipients[$j]['type'] == "youtuber") {
					$stmt = $db->prepare("UPDATE youtuber SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
					$stmt->bindValue(":id", $youtuber['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontacted", time(), Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", $user['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();
				}

				// Assign the steam/switch key/s
				$addTheKeyForTheRecipient = function($typeId, $fromUserId, $platform) use ($db, $recipients, $j, $assignsSingleKeys_id) {
					$stmt = $db->prepare("UPDATE game_key
											SET assigned = :assigned,
												assignedToType = :assignedToType,
												assignedToTypeId = :assignedToTypeId,
												assignedByUser = :assignedByUser,
												assignedByUserTimestamp = :assignedByUserTimestamp
											WHERE id = :id ");
					$stmt->bindValue(":id", $assignsSingleKeys_id[$platform], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":assigned", 1, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":assignedToType", $recipients[$j]['type'], Database::VARTYPE_STRING);
					$stmt->bindValue(":assignedToTypeId", $typeId, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":assignedByUser", $fromUserId, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":assignedByUserTimestamp", time(), Database::VARTYPE_INTEGER);
					$stmt->execute();
				};

				if ($assignsSingleKeys['steam']) {
					$addTheKeyForTheRecipient( $recipient_typeId, $user['id'], 'steam' );
				}
				if ($assignsSingleKeys['switch']) {
					echo "adding key for recipient " . $recipient_typeId . ' - ' . $user['id'] . ' switch';
					$addTheKeyForTheRecipient( $recipient_typeId, $user['id'], 'switch' );
				}
			}
		}
	}

	if ($all_sent) {
		$stmt = $db->prepare(" 	UPDATE emailcampaignsimple SET sent = '1', `timestamp` = '" . time() . "' WHERE id = :id;");
		$stmt->bindValue(":id", $campaign['id'], Database::VARTYPE_INTEGER);
		$stmt->execute();
	}


}
echo "<hr/>\n";
echo "Done!";

?>
