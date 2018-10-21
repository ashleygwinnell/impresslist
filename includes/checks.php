<?php

// Run web server & install checks.
global $require_config;
global $impresslist_installed;

// Check the PHP version.
$phpVersion = explode(".", phpversion());
if (($phpVersion[0] < 5) || ($phpVersion[0] == 5 && $phpVersion[1] < 4)) {
	echo "impress[] requires a PHP version >= 5.4.";
	die();
}

// Make sure user has ran Composer.
$documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], "/");
if (!file_exists($documentRoot . "/vendor/autoload.php")) {
	echo "	impress[] requires you to run <pre>composer update</pre> in the terminal to download external PHP libraries.<br/>
			<a href='https://getcomposer.org/'>Download Composer</a>";
	die();
}

// Make sure user has ran Bower.
if (!file_exists($documentRoot . "/js/vendor/bootstrap-multiselect/bower.json")) {
	echo "	impress[] requires you to run <u>bower update</u> in the terminal to download external JS libraries.<br/>
			<a href='http://bower.io/'>Download Bower</a>";
	die();
}

// Make sure user has configured.
if (!file_exists($documentRoot . "/includes/config/config.php") && $require_config) {
	header('Location: install.php');
	die();
}
else if (file_exists($documentRoot . "/includes/config/config.php")) {
	$impresslist_installed = true;
	//include_once("includes/config/config.php");
	//header("Location: /");
	//die();
}

?>
