<?php

require_once './core/utils.php';
require_once './core/database.php';
require_once './User/Model.php';

if(!$_SERVER['REQUEST_METHOD'] === 'POST')
{
	header('HTTP/1.0 405 Method Not Allowed', 405);
	die();
}

$email = $_POST['Email'];
$password = $_POST['Password'];

$db = new Db();
$stmt = $db->prepare('SELECT * FROM Users WHERE Email = :email');
$stmt->bindParam(':email', $email, PDO::PARAM_STR);

if(!$stmt->execute())
{
	//El failio
}

$data = $stmt->fetchAll();

if(count($data) !== 1)
{
	//El failio
}

$partialUsr = new User($data[0]);

$inputPassHash = hash('sha512', $password . $partialUsr->PassSalt, FALSE);

if($partialUsr->PassHash != $inputPassHash)
{
	header("Location: /login.php?err=UsernameOrPassword");
	exit();
}

session_start();

$_SESSION['auth'] = true;
$_SESSION['User'] = $partialUsr;

header("Location: /index.php"); 


?>