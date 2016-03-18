<?php

set_time_limit(0);

$require_login = false;
$require_config = true;
include_once($_SERVER['DOCUMENT_ROOT'] . "/init.php");

$r = twitter_helpConfigurationSave();
print_r($r);

?>