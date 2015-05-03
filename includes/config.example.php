<?php

if ($_SERVER['HTTP_HOST'] == "localhost") { 
	// .. Local settings.
}

// Rename me to config.php
$impresslist_emailIMAPHost = "imap.yourwebsolution.com";
$impresslist_emailAddress = "inbox@subdomain.yourwebdomain.com";
$impresslist_emailPassword = "password";

$impresslist_backupEmail = "hello@example.com";

$impresslist_databaseType = Database::TYPE_SQLITE;

$impresslist_sqliteDatabaseName = "data/database.sql";
$impresslist_sqliteDatabaseBackupFile = "data/database-backup.sql";

$impresslist_mysqlServer = "yourwebdomain.com";
$impresslist_mysqlUsername = "yourwebd_impress";
$impresslist_mysqlPassword = "password";
$impresslist_mysqlDatabaseName = "yourwebd_impresslist";

$impresslist_cacheType = Cache::TYPE_NONE;
$impresslist_memcacheServer = "localhost";
$impresslist_memcachePort = 11211;

$twitter_consumerKey = "consumer_key";
$twitter_consumerSecret = "consumer_secret";
$twitter_oauthToken = "oauth_token";
$twitter_oauthSecret = "oauth_secret";

?>