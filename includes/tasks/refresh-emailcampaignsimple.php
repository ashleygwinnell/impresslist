<?php

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/libs/Parsedown-1.6.0.php");

error_reporting(E_ALL);

$queue = $db->query("SELECT * FROM emailcampaignsimple WHERE ready = 1 AND sent = 0 AND `timestamp` <= " . time() . " ORDER BY timestamp LIMIT 10;");
//print_r($queue);

$Parsedown = new Parsedown();

for ($i = 0; $i < count($queue); $i++) 
{
	$campaign = $queue[$i];
	$recipients = json_decode($campaign['recipients'], true);
	$user = db_singleuser($db, $campaign['user']);

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
			
			$html_message = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
								<html>
									<head>
										<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
										<meta http-equiv="Content-Language" content="en-us">
									</head>
									<body>' . $html_contents . '</body>
								</html>';


			$success = mail($person_email, $campaign['subject'], $html_message, $headers);
			if (!$success) {
				echo "ERROR sending e-mail.";
				die();
			} else {
				$recipients[$j]['sent'] = true;

				$stmt = $db->prepare(" 	UPDATE emailcampaignsimple 
										SET
											recipients = :recipients
										WHERE id = :id;");
				$stmt->bindValue(":recipients", json_encode($recipients), Database::VARTYPE_STRING); 
				$stmt->bindValue(":id", $campaign['id'], Database::VARTYPE_INTEGER); 
				$stmt->execute();
			}
		}	
	}

	if ($all_sent) {
		$stmt = $db->prepare(" 	UPDATE emailcampaignsimple SET sent = '1' WHERE id = :id;");
		$stmt->bindValue(":id", $campaign['id'], Database::VARTYPE_INTEGER); 
		$stmt->execute();	
	}

	
}
echo "Done!";

?>