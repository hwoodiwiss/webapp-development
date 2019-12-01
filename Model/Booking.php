<?php

require_once __DIR__ .'/../core/database.php';
require_once __DIR__ .'/../Model/User.php';
require_once __DIR__ .'/../Model/Course.php';

class Booking extends DbData
{
	public $Id;
	/** @var integer
	 * 	@fkey-alias User
	 * 	@fkey-table Users
	 */
	public $UserId;
	/** @var integer
	 * 	@fkey-alias Course
	 * 	@fkey-table Courses
	 */
	public $CourseId;
	public $Timestamp;
}

$Bookings = new DbHelper("Booking");

?>