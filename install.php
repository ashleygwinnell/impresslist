<?php
// ---
// install.php
// ---

// 1.
// Set up accounts:
// 	Email account on your server for the system.
// 	Twitter app for pulling in data.

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
// 	includes/tasks/refresh-twitter 			twice every hour.
// 	includes/tasks/refresh-rss 				twice every hour.

// Voila!

?>