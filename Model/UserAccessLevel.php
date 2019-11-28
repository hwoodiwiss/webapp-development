<?php

require_once __DIR__ .'\\..\\core\\database.php';

class UserAccessLevel extends DbData
{
	/** @var integer */
	public $Id;
	public $Name;
}

$UserAccessLevels = new DbHelper('UserAccessLevel');

?>