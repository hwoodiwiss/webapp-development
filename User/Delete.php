<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../core/utils.php';
require_once '../Model/User.php';

if(!(GetRequestMethod() == 'POST'))
{
	header('HTTP/1.0 405 Method Not Allowed', false,  405);
	exit();
}

StartSession();
RequireAuth();
$User = SafeGetValue($_SESSION, "User");

if(!$User->AccessLevel->Name == "Admin")
{
	header("Unauthorised", false, 401);
	die();
}

$UserId = ValidatePOSTValue("Id", true);

$DeleteUser = $Users->Find($UserId);
$DeleteUser->Active = false;
$Users->UpdateObj($DeleteUser);

header("Location: /Users.php");
exit();

?>