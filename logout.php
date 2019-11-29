<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once './core/utils.php';

StartSession();
if (SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true) {
	header('Location: /login.php');
	exit();
}

if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	session_destroy();
	header('Location: /login.php');
	exit();
}
?>