<?php

set_time_limit(0);

$require_login = false;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$queue = $db->query("SELECT * FROM emailqueue WHERE sent = 0 ORDER BY timestamp LIMIT 10;");
//print_r($queue);

for($i = 0; $i < count($queue); $i++) {

	$email = $queue[$i];

	echo "Sending e-mail '" . $email['subject'] . "' to " . $email['to_address'] . "...";
	if (mail($email['to_address'], $email['subject'], $email['message'], $email['headers'])) {
	    // Delete it from this list. 
	    $stmt = $db->prepare("UPDATE emailqueue SET sent = '1' WHERE id = '" . $email['id'] . "'; ");
	    $stmt->execute();
	    
	    echo "OK!<br/>"; 
	} else {
	    echo "ERROR!<br/>";
	}
	
	sleep(1);
}
echo "Done!";

?>