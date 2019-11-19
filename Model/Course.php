<?php

require_once __DIR__ .'\\..\\core\\data.php';

class Course extends DBData
{
	public $Id;
	public $Name;
	public $StartDate;
	public $Duration;
	public $Capacity;
	public $Active;
}

?>