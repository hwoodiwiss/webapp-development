<?php

require_once './core/utils.php';
require_once './core/database.php';
require_once './Model/User.php';

if(!$_SERVER['REQUEST_METHOD'] === 'POST')
{
	header('HTTP/1.0 405 Method Not Allowed', false,  405);
	die();
}

$email = $_POST['Email'];
$password = $_POST['Password'];
$location = SafeGetValue($_POST, 'location');

$FoundUsers = $Users->Select([], [new DbCondition("Email", $email)]);

if(count($FoundUsers) !== 1)
{
	//El failio
}

$User = $FoundUsers[0];

$inputPassHash = hash('sha512', $password . $User->PassSalt, FALSE);

if($User->PassHash != $inputPassHash)
{
	header("Location: /login.php?err=e01" . ($location != null ? "&location=" . $location : ""));
	exit();
}

StartSession();

$_SESSION['auth'] = true;
$_SESSION['User'] = $User;

if($location != null)
{
	header("Location: " . urldecode($location));
	exit();
}

header("Location: /index.php"); 


?>