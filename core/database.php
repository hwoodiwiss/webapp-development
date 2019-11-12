<?php 
	
	include_once 'data.php';
	include_once 'utils.php';

	class DB extends PDO
	{
		public function __construct($file = __DIR__ . "\\..\\settings.ini")
		{
			if(!$settings = parse_ini_file($file, TRUE))
				throw new exception("Cannot open settings file " . $file);
			$connString = $settings['database']['driver'] .
				':host=' . $settings['database']['host'] .
				((!empty($settings['database']['port'])) ? (';port=' . $settings['database']['port']):'').
				';dbname=' . $settings['database']['database'];

				parent::__construct($connString, $settings['database']['username'], $settings['database']['password']);
		}

	}

?>