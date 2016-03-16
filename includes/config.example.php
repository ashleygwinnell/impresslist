<?php

// -----------------------
// Rename me to config.php
// -----------------------

$impresslist_company_name = "Company Name";
$impresslist_company_addressLine = "Company Name, 1 Tree Hill, City, Country.";
$impresslist_company_emailAddress = "contact@yourwebdomain.com";
$impresslist_company_twitter = "http://twitter.com/company_name";
$impresslist_company_facebook = "http://facebook.com/company_name";

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

// https://apps.twitter.com/
$twitter_consumerKey = "consumer_key";
$twitter_consumerSecret = "consumer_secret";
$twitter_oauthToken = "oauth_token";
$twitter_oauthSecret = "oauth_secret";

// https://developers.facebook.com
$facebook_appId = "app_id";
$facebook_appSecret = "app_secret";
$facebook_apiVersion = "api_version";

$youtube_apiKey = "youtube_api_key";

$slack_enabled = true;
$slack_apiUrl = "https://hooks.slack.com/services/GENERATE/THIS/URL";

// Local/development settings might be different.
if ($_SERVER['HTTP_HOST'] == "localhost") {

}


?>