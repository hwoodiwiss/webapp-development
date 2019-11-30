<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require_once './Model/User.php';
require_once './Model/Course.php';
require_once './Model/Booking.php';
require_once './core/utils.php';

StartSession();
RequireAuth();

if(GetRequestMethod() != "POST")
{
	ErrorResponse(404);
}

$UserId = ValidatePOSTValue("UserId", true);
$CourseId = ValidatePOSTValue("CourseId", true);
$Now = CurrentDateTime();

$NewBooking = new Booking();
$NewBooking->UserId = $UserId;
$NewBooking->CourseId = $CourseId;
$NewBooking->Timestamp = $Now;

$Bookings->InsertObj($NewBooking);

header('Location: /Courses.php');
exit();
?>

