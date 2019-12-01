<?php
require_once '../../core/utils.php';
require_once '../../Model/User.php';
require_once '../../Model/Course.php';

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

$CourseId = ValidatePOSTValue("Id", true);
$DeleteCourse = $Courses->Find($CourseId);
$DeleteCourse->Active = false;
$Courses->UpdateObj($DeleteCourse);

header("Location: ../Courses.php");
exit();

?>