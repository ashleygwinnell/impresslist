<?php
// ---
// install.php
// ---

$require_login = false;
include_once("init.php");

// delete tables?
$resetdb = true;
/*if (isset($_GET['cleardatabase']) && $_GET['cleardatabase'] == true) { 
	$resetdb = true;
}

if (isset($_GET['doupdate']) && $_GET['doupdate'] == true) {
	//$db->exec("UPDATE person SET lastcontacted = " . (time() - 86400) . " WHERE id = 1;");
}*/

if ($resetdb) { 
	$sql = "DROP TABLE person;";
	$db->exec($sql);

	$sql = "DROP TABLE publication;";
	$db->exec($sql);

	$sql = "DROP TABLE person_publication;";
	$db->exec($sql);

	$sql = "DROP TABLE person_youtuber;";
	$db->exec($sql);

	$sql = "DROP TABLE publication_coverage;";
	$db->exec($sql);

	$sql = "DROP TABLE youtuber_coverage;";
	$db->exec($sql);

	$sql = "DROP TABLE youtuber;";
	$db->exec($sql);

	$sql = "DROP TABLE user;";
	$db->exec($sql);

	$sql = "DROP TABLE email;";
	$db->exec($sql);

	$sql = "DROP TABLE emailcampaignsimple;";
	$db->exec($sql);

	$sql = "DROP TABLE emailqueue;";
	$db->exec($sql);

	$sql = "DROP TABLE game;";
	$db->exec($sql);

	$sql = "DROP TABLE game_keys;";
	$db->exec($sql);

}

// keywords
$autoincrement = "AUTOINCREMENT";
$blobTextDefaultToZero = " DEFAULT '0' ";
if ($db->type == Database::TYPE_MYSQL) {
	$autoincrement = "AUTO_INCREMENT";
	$blobTextDefaultToZero = "";
}

// create persons
$sql = "CREATE TABLE IF NOT EXISTS person (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			firstname VARCHAR(255) NOT NULL,
			surnames VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			priorities VARCHAR(255) NOT NULL,
			assigned INTEGER NOT NULL DEFAULT 0, 
			twitter VARCHAR(255) NOT NULL,
			twitter_followers INTEGER NOT NULL DEFAULT 0,
			twitter_updatedon INTEGER NOT NULL DEFAULT 0,
			notes TEXT NOT NULL,
			lastcontacted INTEGER NOT NULL,
			lastcontactedby INTEGER NOT NULL, 
			removed INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);
if ($resetdb) {
	//$db->exec("INSERT INTO person VALUES (NULL, 'Keith Stuart',	'', 							'1=3,2=1', 'keefstuart', 	" . twitter_countFollowers('keefstuart') . ", 'Met on multiple occasions.\nBirmingham\nRadius Festival\netc. :)', 0, 0); ");
	//$db->exec("INSERT INTO person VALUES (NULL, 'Carter Dotson','', 	'1=0,2=3', 'wondroushippo', " . twitter_countFollowers('wondroushippo') . ", 'Loves Toast Time!', 0, 0); ");
}

// create publications
$sql = "CREATE TABLE IF NOT EXISTS publication (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			name VARCHAR(255) NOT NULL,
			url VARCHAR(255) NOT NULL,
			iconurl VARCHAR(255) NOT NULL,
			iconurl_updatedon INTEGER NOT NULL DEFAULT 0,
			rssfeedurl VARCHAR(255) NOT NULL,
			priorities VARCHAR(255) NOT NULL,
			twitter VARCHAR(255) NOT NULL,
			twitter_followers INTEGER NOT NULL,
			twitter_updatedon INTEGER NOT NULL DEFAULT 0,
			notes TEXT NOT NULL,
			lastpostedon INTEGER NOT NULL,
			lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
			removed INTEGER NOT NULL DEFAULT 0,
			lastscrapedon INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);
// ALTER TABLE publication ADD COLUMN priorities VARCHAR(255) NOT NULL DEFAULT '';
if ($resetdb) { 
	//$db->exec("INSERT INTO publication VALUES (NULL, 'The Guardian', 	'http://www.theguardian.com/uk', 	'http://assets.guim.co.uk/images/favicons/79d7ab5a729562cebca9c6a13c324f0e/32x32.ico', 													'', 0); "); 
	//$db->exec("INSERT INTO publication VALUES (NULL, 'Android Rundown', 'http://www.androidrundown.com/', 	'http://www.androidrundown.com/favicon.ico', 																							'', 0); "); 
	//$db->exec("INSERT INTO publication VALUES (NULL, 'Kotaku', 			'http://kotaku.com/', 				'http://i.kinja-img.com/gawker-media/image/upload/s--8ngyEHLF--/c_fill,fl_progressive,g_center,h_80,q_80,w_80/192oz8eyfa6h5png.png', 	'http://feeds.gawker.com/kotaku/full', 0); "); 
}



// create persons
$sql = "CREATE TABLE IF NOT EXISTS person_publication (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			person INTEGER NOT NULL,
			publication INTEGER NOT NULL,
			email VARCHAR(255) NOT NULL,
			lastcontacted INTEGER NOT NULL,
			lastcontactedby INTEGER NOT NULL
		);";
$db->exec($sql);
if ($resetdb) { 
	//$db->exec("INSERT INTO person_publication VALUES (NULL, 1, 1, 'keith.stuart@theguardian.com', 0); "); 
}


// create youtubes
$sql = "CREATE TABLE IF NOT EXISTS youtuber (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			youtubeId VARCHAR(255) NOT NULL,
			youtubeUploadsPlaylistId VARCHAR(255) NOT NULL,
			name VARCHAR(255) NOT NULL,
			description TEXT NOT NULL,
			email VARCHAR(255) NOT NULL DEFAULT '',
			priorities VARCHAR(255) NOT NULL,
			channel VARCHAR(255) NOT NULL,
			iconurl VARCHAR(255) NOT NULL,
			subscribers TEXT NOT NULL {$blobTextDefaultToZero},
			views TEXT NOT NULL {$blobTextDefaultToZero},
			twitter VARCHAR(255) NOT NULL DEFAULT '',
			twitter_followers INTEGER NOT NULL DEFAULT 0,
			twitter_updatedon INTEGER NOT NULL DEFAULT 0,
			notes TEXT NOT NULL,
			lastpostedon INTEGER NOT NULL,
			lastpostedon_updatedon INTEGER NOT NULL DEFAULT 0,
			removed INTEGER NOT NULL DEFAULT 0,
			lastscrapedon INTEGER NOT NULL DEFAULT 0
		);";
// ALTER TABLE youtuber ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '';
$db->exec($sql);
if ($resetdb) {
	//$db->exec("INSERT INTO youtuber (id, name, channel, iconurl, subscribers, views, notes, lastpostedon, removed) VALUES (NULL, 'Stumpt', 'stumptgamers', '', 1000, 1000, 'multiplayer pc', 0, 0); "); 
}

// create person_youtubechannel
$sql = "CREATE TABLE IF NOT EXISTS person_youtuber (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			person INTEGER NOT NULL,
			youtuber INTEGER NOT NULL
		);";
$db->exec($sql);

// create users
// ALTER TABLE user ADD COLUMN lastactivity INTEGER NOT NULL DEFAULT 0
$sql = "CREATE TABLE IF NOT EXISTS user (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			forename VARCHAR(255) NOT NULL,
			surname VARCHAR(255) NOT NULL,
			email VARCHAR(255) NOT NULL,
			emailGmailIndex INTEGER NOT NULL,
			emailIMAPServer VARCHAR(255) NOT NULL,
			emailIMAPPassword VARCHAR(255) NOT NULL,
			emailIMAPPasswordSalt VARCHAR(255) NOT NULL,
			emailIMAPPasswordIV VARCHAR(255) NOT NULL,
			password VARCHAR(32) NOT NULL,
			currentGame INTEGER NOT NULL,
			coverageNotifications INTEGER NOT NULL DEFAULT 1,
			color VARCHAR(10) NOT NULL DEFAULT '#000000',
			admin INTEGER NOT NULL DEFAULT 0,
			lastactivity INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);
$users = $db->query("SELECT * FROM user;");
if (count($users) == 0) { 
	$db->exec("INSERT INTO user (id, 	forename, 	  surname, 	 email, 				emailGmailIndex, password, 					currentGame, color, 	admin, lastactivity) 
						 VALUES (NULL,  'Firstname', 'Surname',  'admin@website.com', 	'1', 			 '" . md5("password") . "', 1, 			 '#000000', 1, 		0 			); "); 	
}
// if ($resetdb) { 
//	
//	$db->exec("INSERT INTO user VALUES (NULL, 'Nick', 	'Dymond', 	 'nick@forceofhab.it', 			 '1', '" . md5("password") . "', 1, '#000000', 1, 0); "); 
// }

// Email queue system
$sql = "CREATE TABLE IF NOT EXISTS emailqueue (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			subject VARCHAR(255) NOT NULL,
			to_address VARCHAR(255) NOT NULL,
			headers TEXT NOT NULL,
			message TEXT NOT NULL,
			`timestamp` INTEGER NOT NULL,
			sent INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);

// email (simple) camapaign system
$sql = "CREATE TABLE IF NOT EXISTS emailcampaignsimple (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			name VARCHAR(255) NOT NULL,
			subject VARCHAR(255) NOT NULL,
			recipients TEXT NOT NULL,
			markdown TEXT NOT NULL,
			`timestamp` INTEGER NOT NULL,
			user INTEGER NOT NULL,
			ready INTEGER NOT NULL DEFAULT 0,
			sent INTEGER NOT NULL DEFAULT 0,
			removed INTEGER NOT NULL DEFAULT 0
		);"
$db->exec($sql);

// create email boxes
$sql = "CREATE TABLE IF NOT EXISTS email (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL, 
			user_id INTEGER NOT NULL,
			person_id INTEGER NOT NULL,
			utime INTEGER NOT NULL,
			from_email VARCHAR(255) NOT NULL,
			to_email VARCHAR(255) NOT NULL,
			subject varchar(255) NOT NULL,
			contents text NOT NULL,
			unmatchedrecipient INTEGER NOT NULL "//,
			 //PRIMARY KEY(user_id, person_id, utime)
			. "
		);";
$db->exec($sql);
if ($resetdb) { 
//	$db->exec("INSERT INTO email VALUES (NULL, 1, 1, " . (time()-86401) . ",  'ashley@forceofhab.it', 'keith@theguardian.com', 'Hello Keith', 'I made you a game with boogers in it.', 0); "); 
//	$db->exec("INSERT INTO email VALUES (NULL, 1, 1, " . (time()-40000) . ",  'ashley@forceofhab.it', 'keith@theguardian.com', 'Hello Keith', 'Dont you love me, baby?', 0); "); 
//	$db->exec("INSERT INTO email VALUES (NULL, 1, 1, " . (time()) . ",  	   'ashley@forceofhab.it', 'keith@theguardian.com', 'Hello Keith', 'Goodbye', 0); "); 
}

// create game table
$sql = "CREATE TABLE IF NOT EXISTS game (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			name VARCHAR(255),
			iconurl VARCHAR(255) NOT NULL
		);";
$db->exec($sql);
$games = $db->query("SELECT * FROM game;");
if (count($games) == 0) {
	$db->exec("INSERT INTO game VALUES (NULL, 'Untitled Game', ''); "); 
}

// game key storage
$sql = "CREATE TABLE IF NOT EXISTS game_key (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			game INTEGER NOT NULL,
			platform VARCHAR(16) NOT NULL,
			keystring VARCHAR(255) NOT NULL,
			assigned INTEGER NOT NULL DEFAULT 0,
			assignedToType VARCHAR(16) NOT NULL,
			assignedToTypeId INTEGER NOT NULL,
			assignedByUser INTEGER NOT NULL,
			assignedByUserTimestamp INTEGER NOT NULL,
			createdOn INTEGER NOT NULL,
			expiresOn INTEGER NOT NULL
		);";
$db->exec($sql); // todo; add indexes?


// track coverage
$sql = "CREATE TABLE IF NOT EXISTS publication_coverage (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			publication INTEGER DEFAULT 0,
			person INTEGER DEFAULT 0,  
			game INTEGER DEFAULT 0, 
			url VARCHAR(255) NOT NULL,
			title TEXT NOT NULL,
			utime INTEGER NOT NULL DEFAULT 0,
			thanked INTEGER NOT NULL DEFAULT 0,
			removed INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);

// track youtube coverage
$sql = "CREATE TABLE IF NOT EXISTS youtuber_coverage (
			id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
			youtuber INTEGER DEFAULT 0,
			person INTEGER DEFAULT 0,  
			game INTEGER DEFAULT 0, 
			url VARCHAR(255) NOT NULL,
			title TEXT NOT NULL,
			thumbnail TEXT NOT NULL,
			utime INTEGER NOT NULL DEFAULT 0,
			thanked INTEGER NOT NULL DEFAULT 0,
			removed INTEGER NOT NULL DEFAULT 0
		);";
$db->exec($sql);




// add database for storing system variables.
// - last backed up
// - backup to email address
// - backup to email frequency.
// - 

$sql = "CREATE TABLE IF NOT EXISTS settings (
			`key` VARCHAR(255) PRIMARY KEY NOT NULL,
			`value` VARCHAR(255)
		);";
$db->exec($sql);
@$db->exec("INSERT IGNORE INTO settings VALUES ('auto_backup_email', ''); "); 
@$db->exec("INSERT IGNORE INTO settings VALUES ('auto_backup_frequency', 0); "); 
@$db->exec("INSERT IGNORE INTO settings VALUES ('manual_backup_lastbackedupon', 0); "); 

echo "done database";


// databse will be created by now.
//$users = $db->query("SELECT * FROM user");
//
//{
//	$db->exec("INSERT INTO user (id, forename, surname, email, emailGmailIndex, password, currentGame, ")
//}

// 1.
// Set up accounts:
// 	Email account on your server for the system.
// 	Twitter app for pulling in data.
//  YouTube data API thing.
//		https://developers.google.com/youtube/v3/
// 2. 
// Fill in config.example.php and rename to config.php

// 3.
// Update php.ini - set your locale.

// 4. 
// Upload to server.

// 5. 
// Change permissions on fles/folders.
// 	chmod 755 index.php
// 	chmod 755 api.php
// 	...
// 	chmod 777 data/
// 	chmod 777 data/database.sql
// 	chmod 777 data/chat.txt
// 	chmod 644 backup.php

// 6.
// Set up cron tasks.
// 	includes/tasks/refresh-email 				every 10 seconds.
// 	includes/tasks/refresh-email-latest 		every 10 seconds.
// 	includes/tasks/refresh-twitter 				twice every hour.
// 	includes/tasks/refresh-rss 					twice every hour.

// Voila!

?>