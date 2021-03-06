<?php

require_once __DIR__ .'/../core/database.php';

class Course extends DbData
{
	/** @var integer */
	public $Id;
	public $Name;
	public $Description;
	public $StartDate;
	public $Duration;
	/** @var integer */
	public $Capacity;
	/** @var boolean */
	public $Active;
}

$Courses = new DbHelper("Course");

?>