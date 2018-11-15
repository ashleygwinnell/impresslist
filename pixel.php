<?php

// Track things
// Currently only used in mailouts.
if (isset($_GET['type']) && isset($_GET['id']) && isset($_GET['recipient']))
{
	$require_login = false;
	$require_config = true;
	include_once("init.php");

	if ($_GET['type'] == 'simple-mailout') {
		$id = $_GET['id'];
		$recipient = $_GET['recipient'];

		if (is_numeric($id)) {

			// fetch people/publications with this email address
			$stmt = $db->prepare("SELECT * FROM person WHERE email = :email; ");
			$stmt->bindValue(":email", $recipient, Database::VARTYPE_STRING);
			$people = $stmt->query();

			$stmt = $db->prepare("SELECT * FROM person_publication WHERE email = :email; ");
			$stmt->bindValue(":email", $recipient, Database::VARTYPE_STRING);
			$peoplePublications = $stmt->query();

			$stmt = $db->prepare("SELECT * FROM publication WHERE email = :email; ");
			$stmt->bindValue(":email", $recipient, Database::VARTYPE_STRING);
			$publications = $stmt->query();

			$stmt = $db->prepare("SELECT * FROM youtuber WHERE email = :email; ");
			$stmt->bindValue(":email", $recipient, Database::VARTYPE_STRING);
			$youtubers = $stmt->query();

			$mailout = db_singlemailoutsimple($db, $id);

			if ($mailout != null) {

				$updated = false;
				$recipients = json_decode($mailout['recipients'], true);
				for($i = 0; $i < count($recipients); $i++)
				{
					if ($recipients[$i]['type'] == "person")
					{
						for($j = 0; $j < count($people); $j++)
						{
							if ($people[$j]['id'] == $recipients[$i]['person_id']) {
								$recipients[$i]['read'] = true;
								$updated = true;
								break;
							}
						}
					}
					else if ($recipients[$i]['type'] == "personPublication")
					{
						for($j = 0; $j < count($peoplePublications); $j++)
						{
							if ($peoplePublications[$j]['id'] == $recipients[$i]['personPublication_id']) {
								$recipients[$i]['read'] = true;
								$updated = true;
								break;
							}
						}
					}
					else if ($recipients[$i]['type'] == "publication")
					{
						for($j = 0; $j < count($publications); $j++)
						{
							if ($publications[$j]['id'] == $recipients[$i]['publication_id']) {
								$recipients[$i]['read'] = true;
								$updated = true;
								break;
							}
						}
					}
					else if ($recipients[$i]['type'] == "youtuber")
					{
						for($j = 0; $j < count($youtubers); $j++)
						{
							if ($youtubers[$j]['id'] == $recipients[$i]['youtuber_id']) {
								$recipients[$i]['read'] = true;
								$updated = true;
								break;
							}
						}
					}
					if ($updated) {
						break;
					}
				}

				// Save new data of mailout.
				if ($updated) {
					$stmt = $db->prepare(" UPDATE emailcampaignsimple SET recipients = :recipients WHERE id = :id ;");
					$stmt->bindValue(":id", $id, Database::VARTYPE_INTEGER);
					$stmt->bindValue(":recipients", json_encode($recipients), Database::VARTYPE_STRING);
					$stmt->execute();
				}
			}


		}
	}
}

header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');
die();

?>
