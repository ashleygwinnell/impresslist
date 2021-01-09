<?php

ini_set("allow_url_fopen", "On");

$startTime = time();
$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$impresslist_verbose = true;

$r = youtube_v3_getInformation("forceofhabit");
print_r($r);
echo "<br/>";

echo "<b>Done!</b>\n";


?>
