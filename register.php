<?php
session_start();

define('PROJECT_ROOT', __DIR__.'/');
require_once(PROJECT_ROOT.'php/connection.php');

require_once 'php/connection.php';

$username = $_POST['register_username'];
$password = $_POST['register_password'];
$password_confirm = $_POST['register_password_confirm'];

$msg = "";

$user  = R::findOne('user', 'username = ?', [$username]);

if ($user) {

	$msg .= 'alreadyEnrolled';
	
} else {

	if (strcmp($password, $password_confirm) !== 0) {

		$msg .= 'passwordsNotMatch';

	} else {		

		$user = R::dispense('user');
		$user->username = $username;
		$user->password = $password;
		$user->encryptPassword();
		$user->generateHashSalt();
		R::store($user);
		$msg .= 'success';
	}
	
}

header("Location: index.php?msg=".$msg);
