<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/Parsedown-1.6.0.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/phpmailer/class.phpmailer.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/phpmailer/class.smtp.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/phpmailer/class.pop3.php");

$queue = $db->query("SELECT * FROM emailcampaignsimple WHERE ready = 1 AND sent = 0 AND removed = 0 AND `timestamp` <= " . time() . " ORDER BY `timestamp` LIMIT 10;");
//print_r($queue);

$Parsedown = new Parsedown();

for ($i = 0; $i < count($queue); $i++) 
{
	$campaign = $queue[$i];
	$recipients = json_decode($campaign['recipients'], true);
	$user = db_singleuser($db, $campaign['user']);

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

	$all_sent = true;
	for($j = 0; $j < count($recipients); $j++) 
	{
		if ($recipients[$j]['sent'] == 0) 
		{
			$all_sent = false;
			$person = null;
			$person_email = "";
			if ($recipients[$j]['type'] == "person") 
			{
				$person = db_singleperson($db, $recipients[$j]['person_id']);
				$person_email = $person['email'];
			}
			else if ($recipients[$j]['type'] == "personPublication") 
			{
				$perpub = db_singlepersonpublication($db, $recipients[$j]['personPublication_id']);
				$person = db_singleperson($db, $perpub['person']);
				$person_email = $perpub['email'];
			}
			else {
				echo "Skipping e-mail line: " . json_encode($recipients[$j]);
				continue;
			}
				
			echo "Sending e-mail <i>" . $campaign['subject'] . "</i> to " . $person['firstname'] . " " . $person['surnames'] . " (" . $person_email . "). <br/>";
			$markdown = $campaign['markdown'];
			$markdown = str_replace("{{first_name}}", $person['firstname'], $markdown);
			$html_contents = $Parsedown->text($markdown);
			
			$urlroot = (isset($_SERVER['HTTPS'])?"https://":"http://") . $_SERVER['HTTP_HOST'];
			$html_message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
								<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
										<meta http-equiv="Content-Language" content="en-us">
									</head>
									<body>
										' . $html_contents . '
										<img src="' . $urlroot . '/pixel.php?type=simple-mailout&id=' . $campaign['id'] . '&recipient=' . $person_email . '"/>
									</body>
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
			$mail->addAddress($person_email, $person['firstname'] . " " . $person['surnames']);
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

				// Add this email to the general list of e-mails between people. 
				$stmt = $db->prepare("INSERT INTO email (id, 	user_id, 	person_id, 	utime, 	from_email,  to_email, 	subject,  contents, unmatchedrecipient   )
													VALUES  (NULL, :user_id, 	:person_id, :utime, :from_email, :to_email, :subject, :contents, :unmatchedrecipient );");
				$stmt->bindValue(":user_id", $user['id'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":person_id", $person['id'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":utime", time(), Database::VARTYPE_INTEGER);
				$stmt->bindValue(":from_email", $user['email'], Database::VARTYPE_STRING);
				$stmt->bindValue(":to_email", $person_email, Database::VARTYPE_STRING);
				$stmt->bindValue(":subject", $campaign['subject'], Database::VARTYPE_STRING);
				$stmt->bindValue(":contents", $html_message, Database::VARTYPE_STRING);
				$stmt->bindValue(":unmatchedrecipient", 0, Database::VARTYPE_INTEGER);
				$stmt->execute();

				$stmt = $db->prepare("UPDATE person SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
				$stmt->bindValue(":id", $person['id'], Database::VARTYPE_INTEGER);
				$stmt->bindValue(":lastcontacted", time(), Database::VARTYPE_INTEGER);
				$stmt->bindValue(":lastcontactedby", $user['id'], Database::VARTYPE_INTEGER);
				$stmt->execute();

				if ($recipients[$j]['type'] == "personPublication")  {
					$stmt = $db->prepare("UPDATE person_publication SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE person = :person_id AND publication = :pulication_id ");
					$stmt->bindValue(":person_id", $person['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":publication_id", $perpub['publication'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontacted", time(), Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", $user['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();
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
echo "Done!";

?>