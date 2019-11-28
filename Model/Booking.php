<?php

require_once __DIR__ .'\\..\\core\\database.php';


class Booking extends DbData
{
	public $Id;
	public $UserId;
	public $CourseId;
}

$Bookings = new DbHelper("Booking");

?>