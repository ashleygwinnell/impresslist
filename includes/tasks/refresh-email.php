<?php

set_time_limit(0);

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

//for($loop = 0; $loop < 6; $loop++) {

	$imap_connection = imap_open("{" . $impresslist_emailIMAPHost . ":993/imap/ssl/novalidate-cert}INBOX", $impresslist_emailAddress, $impresslist_emailPassword);

	//print_r($imap_connection);
	//echo $mbox;

	//$inbox_count = imap_num_recent($imap_connection);
	$inbox_count = imap_num_msg($imap_connection);

	$in = array();
	for($i = 1; $i <= 30 && $i <= $inbox_count; $i++) {
		$header = imap_headerinfo($imap_connection, $i);
		//print_r($header);
		$in[] = array(
			//'header' 		=> $header,
	        'id'   			=> $i,
	        //'msgno'   			=> $header->Msgno,
	        'from' 			=> $header->from[0]->mailbox . "@" . $header->from[0]->host,
	        'fromFormatted' => htmlspecialchars($header->fromaddress),
	        'to'			=> $header->to[0]->mailbox . "@" . $header->to[0]->host,
	        'toFormatted' 	=> htmlspecialchars($header->toaddress),
	        'subject' 		=> $header->subject,
	        'contents'		=> quoted_printable_decode( imap_fetchbody($imap_connection, $i, "2") ),
	        'timestamp' 	=> $header->udate,
	        'datef' 		=> date('l dS F Y, H:i', $header->udate),
	        'deleted' 		=> (($header->Deleted == "D")?"true":"false")
	    );
	}



	for($i = 0; $i < count($in); $i++)
	{
		$from = strtolower( $in[$i]['from'] );
		$to = strtolower( $in[$i]['to'] );

		//echo $in[$i]['from'] . "<br/>";
		$stmt = $db->prepare("SELECT * from user WHERE email = :email LIMIT 1");
		$stmt->bindValue(":email", $from, Database::VARTYPE_STRING);
		$userResults = $stmt->query();

		$count = count($userResults);
		if ($count == 0) {
			// flag this email as being sent from a non impresslist email.
			echo "Found an email sent from a non impresslist email account?<br/>";
			echo $from . "<br/>";
		} else if ($count == 1) {
			// add this to email db and delete the email.
			echo "ADD EMAIL TO DATABASE<br/>";
			echo $from . "<br/>";
			echo $to . "<br/>";


			// Make sure this user is in the database.
			$count_recipients_1 = 0;
			$count_recipients_2 = 0;

			$stmt2 = $db->prepare("SELECT * FROM person WHERE email = :email AND removed = 0; ");
			$stmt2->bindValue(":email", $to, Database::VARTYPE_STRING);
			$rs2arr = $stmt2->query();
			$count_recipients_1 = count($rs2arr);

			$stmt3 = $db->prepare("SELECT * FROM person_publication WHERE email = :email; ");
			$stmt3->bindValue(":email", $to, Database::VARTYPE_STRING);
			$rs3arr = $stmt3->query();
			$count_recipients_2 = count($rs3arr);

			$stmt4 = $db->prepare("SELECT * FROM publication WHERE email = :email AND removed = 0; ");
			$stmt4->bindValue(":email", $to, Database::VARTYPE_STRING);
			$rs4arr = $stmt4->query();
			$count_recipients_3 = count($rs4arr);

			$stmt5 = $db->prepare("SELECT * FROM youtuber WHERE email = :email AND removed = 0; ");
			$stmt5->bindValue(":email", $to, Database::VARTYPE_STRING);
			$rs5arr = $stmt5->query();
			$count_recipients_4 = count($rs5arr);

			// TODO: check email accounts on publications
			// TODO: check email accounts on youtubers

			if ($count_recipients_1 > 1 || $count_recipients_2 > 1 || $count_recipients_3 > 1 || $count_recipients_4 > 1) {
				echo "THIS EMAIL ADDRESS IS IN THE DATABASE FOR TWO PEOPLE. CANNOT ADD EMAIL.<br/>";
				continue;
			} else if ($count_recipients_1 == 0 && $count_recipients_2 == 0 && $count_recipients_3 == 0 && $count_recipients_4 == 0) {
				echo "ADD RECIPIENT TO DATABASE<br/>";
				$stmt4 = $db->prepare("INSERT INTO person (id, 	firstname,  surnames, email,  priorities,  twitter,  twitter_followers,  notes,  lastcontacted, lastcontactedby, removed)
												  VALUES (NULL, :firstname, :surnames, :email, :priorities, :twitter, :twitter_followers, :notes, :lastcontacted, :lastcontactedby, :removed);");
				$stmt4->bindValue(":firstname", $to, Database::VARTYPE_STRING);
				$stmt4->bindValue(":surnames", "", Database::VARTYPE_STRING);
				$stmt4->bindValue(":email", $to, Database::VARTYPE_STRING);
				$stmt4->bindValue(":priorities", db_defaultPrioritiesString($db), Database::VARTYPE_STRING);
				$stmt4->bindValue(":twitter", "", Database::VARTYPE_STRING);
				$stmt4->bindValue(":twitter_followers", 0, Database::VARTYPE_INTEGER);
				$stmt4->bindValue(":notes", "Automatically generated.", Database::VARTYPE_STRING);
				$stmt4->bindValue(":lastcontacted", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
				$stmt4->bindValue(":lastcontactedby", $userResults[0]['id'], Database::VARTYPE_INTEGER);
				$stmt4->bindValue(":removed", 0, Database::VARTYPE_INTEGER);
				$rs4 = $stmt4->execute();

				$rs2arr[] = array("id" => $db->lastInsertRowID());
				$count_recipients_1 = 1;
				$count_recipients_2 = 0;


			}

			// person and personPublications
			if (
				(($count_recipients_1 == 1 && $count_recipients_2 == 0) || ($count_recipients_1 == 0 && $count_recipients_2 == 1)) && $count_recipients_3 == 0 && $count_recipients_4 == 0
			)
			{
				// we have this recipient, so just just the email!
				echo "RECIPIENT EXISTS. ADD JUST THE EMAIL.<br/>";
				$result = (count($rs2arr) > 0)?$rs2arr[0]:$rs3arr[0];
				$person_id = (count($rs2arr) > 0)?$rs2arr[0]['id']:$rs3arr[0]['person'];

				$would_be_dupe = util_wouldEmailBeDuplicate($userResults[0]['id'], $in[$i]['timestamp'], $from, $to, $in[$i]['subject'], $in[$i]['contents']);

				if ($would_be_dupe) {
					echo "already in database (dupe: " . $would_be_dupe . ") / deleting! <br/>";
					imap_delete($imap_connection, $in[$i]['id']);
				//	print_r($result);
				} else {
					echo "add email to database<br/>";
					echo "dupe? " . (($would_be_dupe)?"true":"false") . "<br/>";

					$stmt = $db->prepare("INSERT INTO email (id, 	user_id, 	person_id, 	utime, 	from_email,  to_email, 	subject,  contents, unmatchedrecipient   )
													VALUES  (NULL, :user_id, 	:person_id, :utime, :from_email, :to_email, :subject, :contents, :unmatchedrecipient );");
					$stmt->bindValue(":user_id", $userResults[0]['id'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":person_id", $person_id, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":utime", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":from_email", $from, Database::VARTYPE_STRING);
					$stmt->bindValue(":to_email", $to, Database::VARTYPE_STRING);
					$stmt->bindValue(":subject", $in[$i]['subject'], Database::VARTYPE_STRING);
					$stmt->bindValue(":contents", $in[$i]['contents'], Database::VARTYPE_STRING);
					$stmt->bindValue(":unmatchedrecipient", 0, Database::VARTYPE_INTEGER);
					$stmt->execute();
					/*if ($stmt->execute() === FALSE) {
						echo $userResults[0]['id'] . "<br/>";
						echo $person_id . "<br/>";
						print_r($in[$i]);
					}*/
					//$stmt->close();

					// update "last contacted" for this person.
					$stmt = $db->prepare("UPDATE person SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
					$stmt->bindValue(":id", $person_id, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontacted", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
					$stmt->bindValue(":lastcontactedby", $userResults[0]['id'], Database::VARTYPE_INTEGER);
					$stmt->execute();
					//$stmt->close();

					if (count($rs2arr) == 0) {
						$stmt = $db->prepare("UPDATE person_publication SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE person = :person_id AND publication = :pulication_id ");
						$stmt->bindValue(":person_id", $person_id, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":publication_id", $rs3arr['publication'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontacted", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontactedby", $userResults[0]['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
						//$stmt->close();
					}

					// todo: delete the email.

				}

			} else if ($count_recipients_1 >= 1 && $count_recipients_2 >= 1) {
				echo "Cannot add email to database. the recipient's email was found twice and the system got confused...<br/>";
			}

			// publications
			if ($count_recipients_1 == 0 && $count_recipients_2 == 0 && $count_recipients_3 > 0 && $count_recipients_4 == 0) {
				if ($count_recipients_3 > 1) {
					echo "RECIPIENT (PUBLICATION) EXISTS TWICE<br/>";
				}
				else {
					echo "RECIPIENT (PUBLICATION) EXISTS. ADD JUST THE EMAIL.<br/>";
					//lastcontacted
					$result = $rs4arr[0]; 				//(count($rs4arr) > 0)?$rs2arr[0]:$rs3arr[0];
					$publication_id = $result['id']; 	// (count($rs4arr) > 0)?$rs2arr[0]['id']:$rs3arr[0]['person'];

					$would_be_dupe = util_wouldEmailBeDuplicate($userResults[0]['id'], $in[$i]['timestamp'], $from, $to, $in[$i]['subject'], $in[$i]['contents']);

					if ($would_be_dupe) {
						echo "already in database (dupe: " . $would_be_dupe . ") / deleting! <br/>";
						imap_delete($imap_connection, $in[$i]['id']);
					} else {
						echo "Adding...<br/>";

						$stmt = $db->prepare("INSERT INTO email (id, 	user_id, 	publication_id, 	utime, 	from_email,  to_email, 	subject,  contents, unmatchedrecipient   )
														VALUES  (NULL, :user_id, 	:publication_id, :utime, :from_email, :to_email, :subject, :contents, :unmatchedrecipient );");
						$stmt->bindValue(":user_id", $userResults[0]['id'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":publication_id", $publication_id, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":utime", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":from_email", $from, Database::VARTYPE_STRING);
						$stmt->bindValue(":to_email", $to, Database::VARTYPE_STRING);
						$stmt->bindValue(":subject", $in[$i]['subject'], Database::VARTYPE_STRING);
						$stmt->bindValue(":contents", $in[$i]['contents'], Database::VARTYPE_STRING);
						$stmt->bindValue(":unmatchedrecipient", 0, Database::VARTYPE_INTEGER);
						$stmt->execute();

						// update "last contacted" for this publication.
						$stmt = $db->prepare("UPDATE publication SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id LIMIT 1; ");
						$stmt->bindValue(":id", $publication_id, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontacted", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontactedby", $userResults[0]['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
					}
				}
			}

			// youtubers
			if ($count_recipients_1 == 0 && $count_recipients_2 == 0 && $count_recipients_3 == 0 && $count_recipients_4 > 0) {
				if ($count_recipients_4 > 1) {
					echo "RECIPIENT (YOUTUBER) EXISTS TWICE<br/>";
				}
				else {
					echo "RECIPIENT (YOUTUBER) EXISTS. ADD JUST THE EMAIL.<br/>";
					$result = $rs5arr[0];
					$youtuber_id = $result['id'];

					$would_be_dupe = util_wouldEmailBeDuplicate($userResults[0]['id'], $in[$i]['timestamp'], $from, $to, $in[$i]['subject'], $in[$i]['contents']);

					if ($would_be_dupe) {
						echo "already in database (dupe: " . $would_be_dupe . ") / deleting! <br/>";
						imap_delete($imap_connection, $in[$i]['id']);
					} else {
						echo "Adding...<br/>";

						$stmt = $db->prepare("INSERT INTO email (id, 	user_id, 	youtuber_id, 	utime, 	from_email,  to_email, 	subject,  contents, unmatchedrecipient   )
														VALUES  (NULL, :user_id, 	:youtuber_id, :utime, :from_email, :to_email, :subject, :contents, :unmatchedrecipient );");
						$stmt->bindValue(":user_id", $userResults[0]['id'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":youtuber_id", $youtuber_id, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":utime", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":from_email", $from, Database::VARTYPE_STRING);
						$stmt->bindValue(":to_email", $to, Database::VARTYPE_STRING);
						$stmt->bindValue(":subject", $in[$i]['subject'], Database::VARTYPE_STRING);
						$stmt->bindValue(":contents", $in[$i]['contents'], Database::VARTYPE_STRING);
						$stmt->bindValue(":unmatchedrecipient", 0, Database::VARTYPE_INTEGER);
						$stmt->execute();

						// update "last contacted" for this youtuber.
						$stmt = $db->prepare("UPDATE youtuber SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
						$stmt->bindValue(":id", $youtuber_id, Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontacted", $in[$i]['timestamp'], Database::VARTYPE_INTEGER);
						$stmt->bindValue(":lastcontactedby", $userResults[0]['id'], Database::VARTYPE_INTEGER);
						$stmt->execute();
					}
				}
			}

			//print_r($in[$i]);
			//print_r($userResults);

		}
		echo "<hr/>";
	}
	//echo "---<br/>";

	if ($inbox_count == 0) {
		echo "Inbox is empty<br/>";
	}

	//print_r($in);

	/*for($i = 0; $i < count($in); $i++) {
		//print_r($in);
		echo "-----<br/>";
		//echo "from: " . $in[0]['from'] . "<br/>";
		echo "from: " . $in[$i]['from'] . "<br/>";
		echo "time: " . $in[$i]['datef'] . "<br/>";
		echo "subject: " . $in[$i]['subject'] . "<br/>";
		echo "-----<br/>";
		echo $in[$i]['contents'] . "<br/>";
		echo "-----<br/>";
		echo "deleted: " . $in[$i]['deleted'] . "<br/>";
		echo "-----<br/>";
	}
	if (count($in > 0)) {
		imap_delete($imap_connection, $in[0]['index']);
	}
	*/
	imap_expunge($imap_connection);
	imap_close($imap_connection);

//	sleep(9);
//}

echo "done.";

?>
