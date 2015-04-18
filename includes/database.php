<?php


	$db = Database::getInstance();

	function sqlite_epoch($time = 0) {
		return date("Y-m-d H:i:s", $time);
	}
	function db_singleuser($db, $userId) {
		if (!is_numeric($userId)) { return false; }
		$results = $db->query("SELECT * FROM user WHERE id = '" . $userId . "' LIMIT 1;");
		return $results[0];
	}
	function db_singleperson($db, $personId) {
		$rs = $db->query("SELECT *, strftime('%s', lastcontacted) as lastcontacted_timestamp FROM person WHERE id = '" . $personId . "' LIMIT 1;");
		return $rs[0];
	}
	function db_singlepersonpublication($db, $personPublicationId) {
		$people = $db->query("SELECT *, strftime('%s', lastcontacted) as lastcontacted_timestamp FROM person_publication WHERE id = '" . $personPublicationId . "' LIMIT 1;");
		return $people[0];
	}
	function db_singlepublication($db, $publicationId) {
		if (!is_numeric($publicationId)) { return false; }
		$publications = $db->query("SELECT * FROM publication WHERE id = '" . $publicationId . "' LIMIT 1;");
		return $publications[0];
	}
	function db_singleyoutubechannel($db, $youtuberId) {
		if (!is_numeric($youtuberId)) { return false; }
		$youtubeChannels = $db->query("SELECT * FROM youtuber WHERE id = '" . $youtuberId . "' LIMIT 1;");
		return $youtubeChannels[0];
	}
	function db_defaultPrioritiesString($db) {
		$string = "";
		$count = 0;
		$results = $db->query("SELECT * FROM game;");
		foreach ($results as $result) {
			if ($count > 0) {
				$string .= ",";
			}
			$string .= $result['id'] . "=0";
			$count += 1;
		}
		return $string;
	}

	// delete tables?
	$resetdb = false;
	if (isset($_GET['cleardatabase']) && $_GET['cleardatabase'] == true) { 
		$resetdb = true;
	}

	if (isset($_GET['doupdate']) && $_GET['doupdate'] == true) {
		//$db->exec("UPDATE person SET lastcontacted = " . (time() - 86400) . " WHERE id = 1;");
	}

	if ($resetdb) { 
		$sql = "DROP TABLE person;";
		$db->exec($sql);

		$sql = "DROP TABLE publication;";
		$db->exec($sql);

		$sql = "DROP TABLE person_publication;";
		$db->exec($sql);

		$sql = "DROP TABLE user;";
		$db->exec($sql);

		$sql = "DROP TABLE email;";
		$db->exec($sql);

		$sql = "DROP TABLE game;";
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
				name VARCHAR(255) NOT NULL,
				email VARCHAR(255) NOT NULL,
				priorities VARCHAR(255) NOT NULL,
				assigned INTEGER NOT NULL DEFAULT 0, 
				twitter VARCHAR(255) NOT NULL,
				twitter_followers INTEGER NOT NULL DEFAULT 0,
				notes TEXT NOT NULL,
				lastcontacted TIMESTAMP NOT NULL,
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
				rssfeedurl VARCHAR(255) NOT NULL,
				priorities VARCHAR(255) NOT NULL,
				twitter VARCHAR(255) NOT NULL,
				twitter_followers INTEGER NOT NULL,
				notes TEXT NOT NULL,
				lastpostedon TIMESTAMP NOT NULL,
				removed INTEGER NOT NULL DEFAULT 0
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
				lastcontacted TIMESTAMP NOT NULL,
				lastcontactedby INTEGER NOT NULL
			);";
	$db->exec($sql);
	if ($resetdb) { 
		//$db->exec("INSERT INTO person_publication VALUES (NULL, 1, 1, 'keith.stuart@theguardian.com', 0); "); 
	}


	// create youtubes
	$sql = "CREATE TABLE IF NOT EXISTS youtuber (
				id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
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
				notes TEXT NOT NULL,
				lastpostedon INTEGER NOT NULL,
				removed INTEGER NOT NULL DEFAULT 0
			);";
	// ALTER TABLE youtuber ADD COLUMN email VARCHAR(255) NOT NULL DEFAULT '';
	$db->exec($sql);
	if ($resetdb) {
		//$db->exec("INSERT INTO youtuber (id, name, channel, iconurl, subscribers, views, notes, lastpostedon, removed) VALUES (NULL, 'Stumpt', 'stumptgamers', '', 1000, 1000, 'multiplayer pc', 0, 0); "); 
	}
	

	
	// create users
	// ALTER TABLE user ADD COLUMN lastactivity INTEGER NOT NULL DEFAULT 0
	$sql = "CREATE TABLE IF NOT EXISTS user (
				id INTEGER PRIMARY KEY {$autoincrement} NOT NULL,
				forename VARCHAR(255) NOT NULL,
				surname VARCHAR(255) NOT NULL,
				email VARCHAR(255) NOT NULL,
				emailGmailIndex INTEGER NOT NULL,
				password VARCHAR(32) NOT NULL,
				currentGame INTEGER NOT NULL,
				color VARCHAR(10) NOT NULL DEFAULT '#000000',
				admin INTEGER NOT NULL DEFAULT 0,
				lastactivity INTEGER NOT NULL DEFAULT 0
			);";
	$db->exec($sql);
	// if ($resetdb) { 
	//	$db->exec("INSERT INTO user VALUES (NULL, 'Ashley', 'Gwinnell',  'ashley@forceofhab.it', 		 '1', '" . md5("password") . "', 1, '#000000', 1, 0); "); 
	//	$db->exec("INSERT INTO user VALUES (NULL, 'Nick', 	'Dymond', 	 'nick@forceofhab.it', 			 '1', '" . md5("password") . "', 1, '#000000', 1, 0); "); 
	// }

	// create email boxes
	$sql = "CREATE TABLE IF NOT EXISTS email (
				id INTEGER PRIMARY KEY {$autoincrement} NOT NULL, 
				user_id INTEGER NOT NULL,
				person_id INTEGER NOT NULL,
				utime TIMESTAMP NOT NULL,
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
	if ($resetdb) { 
		$db->exec("INSERT INTO game VALUES (NULL, 'Friendship Club', 'http://timmybibble.com/_images/favicon.png'); "); 
		$db->exec("INSERT INTO game VALUES (NULL, 'Sinking Feeling', ''); "); 
	}

	//$db->close();


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

	


?>