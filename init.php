<?php

session_start();

include_once("includes/database.class.php");

include_once("includes/config.php");
include_once("includes/util.php");
include_once("includes/database.php");

//if (!is_ssl()) {
//	header("Location: " . "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
//	die();
//}
global $require_login;
if ($require_login) { 

	if (!isset($_SESSION['user']) || !$_SESSION['user']) {

		$errors = array();
		if (isset($_POST['email']) && isset($_POST['password'])) {

			$email = $_POST['email'];
			$password = $_POST['password'];
			if (!util_isEmail($email)) {
				$errors[] = "Invalid email address.";
			}
			if (empty($errors)) {

				$stmt = $db->prepare("SELECT * FROM user WHERE email = :email AND password = :password; ");
				$stmt->bindValue(":email", $email, Database::VARTYPE_STRING);
				$stmt->bindValue(":password", md5($password), Database::VARTYPE_STRING);
				$results = $stmt->query();

				$count = count($results);
				if ($count == 0) {
					$errors[] = "Invalid email address / password combination. ";
				} else if ($count > 1 || $count < 0) { 
					$errors[] = "Invalid email address / password combination. ";
				} else { 
					$_SESSION['user'] = $results[0]['id'];
					header("Location: /");
					die();
				}
			}
		} 
		if (strpos($_SERVER['REQUEST_URI'], "api.php") !== FALSE) {
			$api_logout = new stdClass();
			$api_logout->success = false;
			$api_logout->message = "You are not logged in.";
			$api_logout->logout = true;
			echo json_encode($api_logout);
			die();
		}
		include_once("includes/login.html");
		die();

	} else { 
		$user = db_singleuser($db, $_SESSION['user']);
		//$user = array("id" => 1, "emailGmailIndex" => 1, "currentGame" => 1);
		$user_id = $user['id'];
		$user_gmailIndex = $user['emailGmailIndex'];
		$user_currentGame = $user['currentGame'];
		$user_admin = ($user['admin'] == 1)?true:false;;
	}
}




?>