<?php

require_once __DIR__ .'\\..\\core\\data.php';

class User extends DBData
{
	public $Id;
	public $Email;
	public $PassHash;
	public $PassSalt;
	public $AccessLevel;
	public $FirstName;
	public $LastName;
	public $Timestamp;
}

?>