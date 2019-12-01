<?php
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
	if(IsAjax())
	{
		echo new ResponseData(false, "", ["Heading" => "Booking Not Deleted!", "Content" => "Something went wrong, so the booking was not deleted!", "Type" => "danger"]);
		exit();
	}
	else
	{
		ErrorResponse(401);
		die();
	}
}

$Bookings->Delete($Booking->Id);

if(IsAjax())
{
	echo new ResponseData(true, "", ["Heading" => "Booking Deleted!", "Content" => "Booking deleted successfully", "Type" => "success"]);
	exit();
}
else
{
	if($Location != null)
	{
		header("Location: " . urldecode($Location));
	}
	else
	{
		header("Location: /index.php");
	}
}
exit();

?>