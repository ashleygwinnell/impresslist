<?php

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$filename = $impresslist_sqliteDatabaseName;
$filename2 = $_SERVER['DOCUMENT_ROOT'] . "/" . $filename;
$filename3 = $_SERVER['DOCUMENT_ROOT'] . "/" . $impresslist_sqliteDatabaseBackupFile;

file_put_contents($filename3, Database::getInstance()->sql());


$backup_email_content = "impresslist backup - " . date('r');
//$backup_email_content .=

$no_reply = "no-reply" . substr($impresslist_emailAddress, strpos($impresslist_emailAddress, "@"));
//echo $no_reply . "<br/>";
mail_attachment(
	$impresslist_sqliteDatabaseBackupFile,
	$_SERVER['DOCUMENT_ROOT'] . "/",
	$impresslist_backupEmail,
	$impresslist_emailAddress,
	"impresslist",
	$no_reply,
	"impresslist backup",
	$backup_email_content
);

/*
// backup.php
$to = $impresslist_backupEmail;
$subject = "impresslist() backup.";
$random_hash = md5(date('r', time()));
$headers  = "From: " . $impresslist_emailAddress . "\r\nReply-To: no-reply@example.com";
$headers .= "\r\nContent-Type: multipart/mixed; boundary=\"PHP-mixed-".$random_hash."\"";
$attachment = chunk_split(base64_encode(file_get_contents($filename2)));
ob_start();

?>
--PHP-mixed-<?php echo $random_hash; ?>
Content-Type: multipart/alternative; boundary="PHP-alt-<?php echo $random_hash; ?>"

--PHP-alt-<?php echo $random_hash; ?>
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

impresslist() backup.
This is a test backup.

--PHP-alt-<?php echo $random_hash; ?>
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit

<h2>impresslist backup</h2>
<p>This is a test backup with <b>HTML</b> formatting.</p>

--PHP-alt-<?php echo $random_hash; ?>--

--PHP-mixed-<?php echo $random_hash; ?>
Content-Type: application/x-sqlite3; name="database.sql"
Content-Transfer-Encoding: base64
Content-Disposition: attachment

<?php echo $attachment; ?>
--PHP-mixed-<?php echo $random_hash; ?>--

<?php
//copy current buffer contents into $message variable and delete current output buffer
$message = ob_get_clean();
//send the email
$mail_sent = @mail( $to, $subject, $message, $headers );
//if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
echo $mail_sent ? "Mail sent" : "Mail failed"; */
?>