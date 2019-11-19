<?php 
	
	include_once 'data.php';
	include_once 'utils.php';

	class DB extends PDO
	{
		
		public function __construct($file = __DIR__ . "\\..\\settings.ini", string $section = "database")
		{
			if(!$settings = parse_ini_file($file, TRUE))
				throw new exception("Cannot open settings file " . $file);
			$connString = $settings[$section]['driver'] .
				':host=' . $settings[$section]['host'] .
				((!empty($settings[$section]['port'])) ? (';port=' . $settings[$section]['port']):'').
				';dbname=' . $settings[$section]['database'];

				parent::__construct($connString, $settings[$section]['username'], $settings[$section]['password']);
		}

	}

?>