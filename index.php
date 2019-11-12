<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once './core/utils.php';
require_once './User/Model.php';

session_start();

if(SafeGetValue($_SESSION, 'auth') !== null && $_SESSION['auth'] == true)
{
	$user = SafeGetValue($_SESSION, 'User');
	echo var_dump($user);
}
else
{
	header('Location: /login.php');
}

?>