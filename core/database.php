<?php 
	
	include_once 'data.php';
	include_once 'utils.php';
	foreach (glob(__DIR__ . "\\..\\Model\\*.php") as $filename)
	{
    	include_once $filename;
	}

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

		public function SelectAll(string $TableName)
		{
			$TableNameLength = strlen($TableName);
			$typeName = substr($TableName, 0, $TableNameLength - 1);

			$typeFields = get_class_vars($typeName);
			$typeFieldsStr = "";
			$numFields = array_count_values($typeFields);
			for($index = 0; $index < $numFields; $index++)
			{
				if($index != 0)
				{
					$typeFieldsStr = $typeFieldsStr . ', ';
				}

				$typeFieldsStr = $typeFieldsStr . $typeFields[$index];
			}

			$stmt = $this->prepare('SELECT ' . $typeFieldsStr . 'FROM ' . $TableName);
			
			if (!$stmt->execute()) 
			{
				die('An error occured:' . $stmt->errorInfo()[2]);
			}

			$data = $stmt->fetchAll();

			$numRows = array_count_values($data);
			$output = array();
			for($index = 0; $index < $numRows; $index++)
			{
				$output[$index] = $typeName($data[$index]);
			}

			return $output;
		}

	}

?>