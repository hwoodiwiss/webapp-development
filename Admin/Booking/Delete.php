<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../../core/utils.php';
require_once '../../Model/User.php';
require_once '../../Model/Booking.php';

if(!(GetRequestMethod() == 'POST'))
{
	ErrorResponse(404);
	exit();
}

StartSession();
RequireAuth();


$BookingId = ValidatePOSTValue("Id", true);
$Location = ValidatePOSTValue("Location");

$User = SafeGetValue($_SESSION, "User");

$Booking = $Bookings->Find($BookingId);

if(!$User->AccessLevel->Name == "Admin" || $Booking->User->Id != $User->Id)
{
	ErrorResponse(401);
	die();
}

$Bookings->Delete($Booking->Id);

if($Location != null)
{
	header("Location: " . urldecode($Location));
}
else
{
	header("Location: /index.php");
}
exit();

?>