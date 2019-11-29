<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../core/utils.php';
require_once '../../Model/User.php';

if(!(GetRequestMethod() == 'POST'))
{
	ErrorResponse(404);
	exit();
}

StartSession();
RequireAuth();
$User = SafeGetValue($_SESSION, "User");

if(!$User->AccessLevel->Name == "Admin")
{
	ErrorResponse(401);
	die();
}

$UserId = ValidatePOSTValue("Id", true);

$DeleteUser = $Users->Find($UserId);
$DeleteUser->Active = false;
$Users->UpdateObj($DeleteUser);

header("Location: ../Users.php");
exit();

?>