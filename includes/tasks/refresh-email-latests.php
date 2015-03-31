<?php

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

// Emails
$emails = array();
$emails_resultset = $db->query("SELECT * FROM email WHERE unmatchedrecipient == 0 ORDER BY utime DESC;");
while($row = $emails_resultset->fetchArray(SQLITE3_ASSOC)) { $emails[] = $row; }
$num_emails = count($emails);

// People
$people = array();
$people_resultset = $db->query("SELECT * FROM person WHERE removed = 0;");
while($row = $people_resultset->fetchArray(SQLITE3_ASSOC)) { $people[] = $row; }
$num_people = count($people);

// Publications
$publications = array();
$publications_resultset = $db->query("SELECT * FROM publication WHERE removed = 0;");
while($row = $publications_resultset->fetchArray(SQLITE3_ASSOC)) { $publications[] = $row; }
$num_publications = count($publications);

// Add Publications to People
$personPublications = array();
$personPublications_resultset = $db->query("SELECT * FROM person_publication;");
while($row = $personPublications_resultset->fetchArray(SQLITE3_ASSOC)) { $personPublications[] = $row; }
$num_personPublications = count($personPublications);

//ALTER TABLE person_publication ADD COLUMN lastcontactedby INTEGER NOT NULL DEFAULT 0;
// Refresh "latest" data on people.
for ($i = 0; $i < count($people); $i++) 
{
	$latestAllContactTimestamp = 0;
	$latestAllContactUser = 0;
	for ($k = 0; $k < count($personPublications); $k++) 
	{
		if ($personPublications[$k]['person'] == $people[$i]['id']) 
		{ 
			$latestEmailIndex = -1;
			$latestEmailTimestamp = 0;
			$latestEmailUser = 0;

			for($j = 0; $j < count($emails); $j++) 
			{
				if ($emails[$j]['to_email'] == $personPublications[$k]['email']) {
					if ($emails[$j]['utime'] > $latestEmailTimestamp) {
						$latestEmailIndex = $j;
						$latestEmailTimestamp = $emails[$j]['utime'];
						$latestEmailUser = $emails[$j]['user_id'];
					}
					if ($emails[$j]['utime'] > $latestAllContactTimestamp) {
						$latestAllContactTimestamp = $emails[$j]['utime'];
						$latestAllContactUser = $emails[$j]['user_id'];
					}
				}
			}
			if ($latestEmailIndex > -1) {
				//echo "updating a thing<br/>";
				//echo $personPublications[$k]['id'] . "<br/>";
				//echo $latestEmailTimestamp . "<br/>";
				//echo $emails[$latestEmailIndex]['user_id'] . "<br/>";
				$stmt = $db->prepare("UPDATE person_publication SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
				$stmt->bindValue(":id", $personPublications[$k]['id'], SQLITE3_INTEGER); 
				$stmt->bindValue(":lastcontacted", $latestEmailTimestamp, SQLITE3_INTEGER);
				$stmt->bindValue(":lastcontactedby", $latestEmailUser, SQLITE3_INTEGER); 
				$stmt->execute();
				$stmt->close();

			}		
		}	
	}


	$latestPersonalEmailTimestamp = 0;
	$latestPersonalEmailIndex = -1;
	for($j = 0; $j < count($emails); $j++) 
	{
		if ($emails[$j]['to_email'] == $people[$i]['email']) {
			if ($emails[$j]['utime'] > $latestPersonalEmailTimestamp) {
				$latestPersonalEmailIndex = $j;
				$latestPersonalEmailTimestamp = $emails[$j]['utime'];
			}
			if ($emails[$j]['utime'] > $latestAllContactTimestamp) {
				$latestAllContactTimestamp = $emails[$j]['utime'];
				$latestAllContactUser = $emails[$j]['user_id'];
			}
		}
	}
	if ($latestAllContactUser > 0) {
		$stmt = $db->prepare("UPDATE person SET lastcontacted = :lastcontacted, lastcontactedby = :lastcontactedby WHERE id = :id; ");
		$stmt->bindValue(":id", $people[$i]['id'], SQLITE3_INTEGER); 
		$stmt->bindValue(":lastcontacted", $latestAllContactTimestamp, SQLITE3_INTEGER);
		$stmt->bindValue(":lastcontactedby", $latestAllContactUser, SQLITE3_INTEGER); 
		$stmt->execute();
		$stmt->close();
	}



}
echo "did persons<br/>";

// Refresh "latest" data on people/publications.

echo "did person/publications<br/>";

// Refresh "latest" data on publications.
echo "done<br/>";



?>