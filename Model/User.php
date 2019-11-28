<?php

require_once __DIR__ .'\\..\\core\\database.php';

class User extends DbData
{
	/** @var integer */
	public $Id;
	public $Email;
	public $PassHash;
	public $PassSalt;
	public $FirstName;
	public $LastName;
	public $JobTitle;
	/** @var integer
	 *  @fkey-alias AccessLevel
	 *  @fkey-table UserAccessLevels
	 */
	public $AccessLevelId;
	public $Timestamp;
	/** @var boolean */
	public $Active;

}

$Users = new DbHelper("User");

?>