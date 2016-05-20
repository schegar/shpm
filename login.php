<?php
session_start();

define('PROJECT_ROOT', __DIR__.'/');
require_once(PROJECT_ROOT.'php/connection.php');

$username = $_POST['login_username'];
$password = $_POST['login_password'];

$msg = "";

$user  = R::findOne('user', 'username = ?', [$username]);


if (!$user) {

	$msg .= 'loginError';
	
} else {

	if ($user->verify($password)) {
		$_SESSION['userid'] = $user->id;
		$options = [
			'cost' => 12,
			'salt' => $user->hashsalt,
		];
		$_SESSION['masterHash'] = substr(password_hash($password, PASSWORD_BCRYPT, $options), 28, 32);
	} else {
		$msg .= 'loginError';
	}	
	
}

header("Location: index.php?category=index&msg=".$msg);
