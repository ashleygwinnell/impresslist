# About

http://impresslist.com/

# Installation

* Enable mod_rewrite in Apache/MAMP/XAMPP.
* Make sure your PHP version is >= 5.4.
* Install Composer.
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('SHA384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"

	mv composer.phar /usr/local/bin/composer

* Install Bower.
	npm install -g bower

* Run Composer.
	composer update

#Notes:

* Make sure sql_mode does not contain 'only_full_group_by'. Fix: https://stackoverflow.com/a/36033983

# README

* Uses Composer for managing PHP dependencies.
* Uses Bower for managing JavaScript dependencies.

