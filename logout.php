<?php
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