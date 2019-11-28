<?php 
	
	include_once 'utils.php';

	class Db extends PDO
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

			if(!class_exists($typeName)) return null;

			$typeFields = get_class_vars($typeName);
			$typeFieldsStr = "";
			$numFields = count($typeFields);
			$count = 0;
			foreach($typeFields as $propName => $propVal)
			{
				if($count != 0)
				{
					$typeFieldsStr = $typeFieldsStr . ', ';
				}

				$typeFieldsStr = $typeFieldsStr . $propName;
				$count++;
			}
			$typeFieldsStr = $typeFieldsStr . ' ';

			$stmt = $this->prepare('SELECT ' . $typeFieldsStr . 'FROM ' . $TableName);
			
			if (!$stmt->execute()) 
			{
				die('An error occured:' . $stmt->errorInfo()[2]);
			}

			$data = $stmt->fetchAll();

			$numRows = count($data);
			$output = array();
			for($index = 0; $index < $numRows; $index++)
			{
				$output[$index] = new $typeName($data[$index]);
			}

			return $output;
		}
		
	}

	//Class to derive DB Data models from
	class DbData
	{
		public function __construct(array $dbObject = null)
		{
			if($dbObject != null)
			{
				$class = get_class($this);
				$props = get_object_vars($this);
				foreach($props as $key => $value)
				{
					$type = GetAnnotatedType($class, $key);
					$this->$key = SafeGetValue($dbObject, $key);
					if($type != "")
					{
						if(!settype($this->$key, $type)) throw new Exception("Invalid conversion from string to " . $type);
					}
				}
			}
		}

		public function GetPartial(array $propNames)
		{
			$props = get_object_vars($this);
			$output = array();
			foreach($propNames as $index => $value)
			{
				$propVal = SafeGetValue($props, $value);
				if($propVal != null)
				{
					$output[$value] = $propVal;
				}
			}

			return $output;
		}

		public static function FromJson(string $jsonString, string $typeName)
		{
			if(!class_exists($typename)) return null;
			$outObj = $typeName();
			$jsobj = json_decode($jsonString, true);
			$props = get_object_vars($outObj);
			foreach($props as $key => $value)
			{
				$outObj->$key = SafeGetValue($jsobj, $key);
			}

			return $outObj;
		}

		public function AsJson()
		{
			return json_encode($this);
		}
	}

	class DbCondition
	{
		public $Column;
		private $Operation;
		public $Value;

		public function __construct(string $Column, $Value, string $Operation = "eq")
		{
			$this->Column = $Column;
			$this->Value = $Value;
			$this->Operation = $Operation;
		}

		public function GetOperation() : string
		{
			$opText = "";
			if($this->Operation == "like")
			{
				$opText = " LIKE ";
			}
			else if($this->Operation == "gt")
			{
				$opText = " > ";
			}
			else if($this->Operation == "lt")
			{
				$opText = " < ";
			}
			else if($this->Operation == "ge")
			{
				$opText = " >= ";
			}
			else if($this->Operation == "le")
			{
				$opText = " <= ";
			}
			else
			{
				$opText = " = ";
			}

			return $opText;
		}
	}

	//SQL Helper class
	class DbHelper
	{
		private $TypeName;
		private $TableName;

		public function __construct(string $TypeName, string $TableName = '')
		{
			if(!class_exists($TypeName)) throw new Exception("Invalid type name proveded");
			if(!in_array("DbData", class_parents($TypeName))) throw new Exception($TypeName . " is not a valid DbData object");

			$this->TypeName = $TypeName;
			if($TableName == '')
			{
				$this->TableName = $TypeName . 's';
			}
			else
			{
				$this->TableName = $TableName;
			}
		}

		public function InsertObj($object)
		{
			if(get_class($object) != $this->TypeName) throw new Exception("Invalid object type for this helper!");

			$objVals = get_object_vars($object);
			$columnVals = array();
			foreach($objVals as $Var => $Val)
			{
				if($Val != null && $Var != "Id")
				{
					$columnVals->$Var = $Val;
				}
			}

			$this->Update($columnVals);
		}

		public function Insert(array $ColumnVals)
		{
			$insertString = "INSERT INTO " . $TableName . " ( " . $this->BuildSelectString($ColumnVals)
			 . ") VALUES ( " . $this->BuildPreparedValuesString($ColumnVals) . ");";

			$db = new Db();
			$stmt = $db->prepare($insertString);

			 $count = 0;
			foreach($ColumnVals as $Column => $Val)
			{
				$paramStr = ":v" + $count;
				if(!$stmt->bindValue($paramStr, $Val, $this->GetPdoParam($Val)))
				{
					throw new Exception("Failed to bind parameter " . $paramStr . " Value: " . $Val);
				}
				$count++;
			}

			if(!$stmt->execute())
			{
				throw new Exception("An error occured updating the database. Error info: " . $stmt->errorInfo()[2]);
			}
		}

		public function Select(array $Columns, array $Conditions = [], string $OrderColumn = "", string $OrderDir = "ASC")
		{
			$colsString = "";
			$conditionString = "";
			$selectString = "SELECT ";

			$colsString = $this->BuildSelectString($Columns);

			$selectString .= $colsString . ' FROM ' . $this->TableName;

			if($Conditions != [])
			{
				$selectString .= $this->BuildConditionString($Conditions);
			}

			if($OrderColumn != "")
			{
				$selectString .= " ORDER BY " . $OrderColumn . " " . $OrderDir;
			}

			$selectString .= ";";

			$db = new Db();
			$stmt = $db->prepare($selectString);

			if($Conditions != [])
			{
				$count = 0;
				foreach($Conditions as $Condition)
				{
					if(get_class($Condition) == "DbCondition")
					{
						$paramStr = ":c" . $count;
						$stmt->bindValue($paramStr, $Condition->Value, $this->GetPdoParam($Condition->Value));
						$count++;
					}
				}
			}

			if(!$stmt->execute())
			{
				throw new Exception("An error occured retrieving data from the database. Error info: " . $stmt->errorInfo()[2]);
			}

			$data = $stmt->fetchAll();

			$numRows = count($data);
			$outData = array();
			for($index = 0; $index < $numRows; $index++)
			{
				$outData[$index] = new $this->TypeName($data[$index]);
			}

			return $outData;

		}

		public function Find(string $Id, array $Columns = [])
		{
			$colsString = $this->BuildSelectString($Columns);
			$selectString = "SELECT " . $colsString . " FROM " . $this->TableName . " WHERE Id = :id;";
			$db = new Db();
			$stmt = $db->prepare($selectString);
			$stmt->bindValue(":id", $Id, $this->GetPdoParam($Id));

			if(!$stmt->execute())
			{
				throw new Exception("An error occured retrieving data from the database. Error info: " . $stmt->errorInfo()[2]);
			}

			$data = $stmt->fetchAll();
			$numRows = count($data);
			$outVal = null;
			if($numRows > 1)
			{
				throw new Exception("A primary key search yeilded multiple results. This should not be possible.");
			}

			if($numRows == 1)
			{
				$outVal = new $this->TypeName($data[0]);
			}

			return $outVal;
		}

		public function UpdateObj($object)
		{
			if(get_class($object) != $this->TypeName) throw new Exception("Invalid object type for this helper!");

			$objVals = get_object_vars($object);
			$columnVals = array();
			foreach($objVals as $Var => $Val)
			{
				if($Val != null && $Var != "Id")
				{
					$columnVals[$Var] = $Val;
				}
			}

			$this->Update($columnVals, [new DbCondition("Id", $object->Id)]);
		}

		public function Update(array $ColumnVals, array $Conditions = [])
		{
			$updateString = "UPDATE " . $this->TableName . " SET" 
			. $this->BuildPreparedValuesString($ColumnVals) . $this->BuildConditionString($Conditions) . ";";

			$db = new Db();
			$stmt = $db->prepare($updateString);

			$count = 0;
			foreach($ColumnVals as $Column => $Val)
			{
				if($Val != null)
				{
					$paramStr = ":v" . $count;
					if(!$stmt->bindValue($paramStr, $Val, $this->GetPdoParam($Val)))
					{
						throw new Exception("Failed to bind parameter " . $paramStr . " Value: " . $Val);
					}
					$count++;
				}
			}

			if($Conditions != [])
			{
				$count = 0;
				foreach($Conditions as $Condition)
				{
					if(get_class($Condition) == "DbCondition")
					{
						$paramStr = ":c" . $count;
						if(!$stmt->bindValue($paramStr, $Condition->Value, $this->GetPdoParam($Condition->Value)))
						{
							throw new Exception("Failed to bind parameter " . $paramStr . " Value: " . $Condition->Value);
						}
						$count++;
					}
				}
			}

			if(!$stmt->execute())
			{
				throw new Exception("An error occured updating the database. Error info: " . $stmt->errorInfo()[2]);
			}
		}

		public function Delete(string $Id)
		{

		}

		private function BuildSelectString(array $Columns) : string
		{
			$colsString = "";
			if($Columns != [])
			{
				$numColumns = count($Columns);
				for($index = 0; $index < $numColumns; $index++)
				{
					if($index != 0)
					{
						$colsString .= ", ";
					}
					
					$colsString .= $Columns[$index];
				}
			}
			else
			{
				$colsString = '*';
			}

			return $colsString;
		}

		private function BuildPreparedValuesString(array $Values) : string
		{
			$valuesString = " ";
			if($Values != [])
			{
				$count = 0;
				foreach($Values as $Column => $Value)
				{
					if($Value != null)
					{
						if($count > 0)
						{
							$valuesString .= ", ";
						}
						$isInt = gettype($Value) == "integer";
						$valuesString .= $Column . " = " . ":v" . $count;
						$count++;
					}
				}
			}

			return $valuesString;
		}

		private function BuildConditionString(array $Conditions) : string
		{
			$conditionString = "";
			if($Conditions != [])
			{
				$conditionString .= ' WHERE ';
				$count = 0;
				foreach($Conditions as $Condition)
				{
					if(get_class($Condition) == "DbCondition")
					{
						if($count > 0)
						{
							$conditionString .= " AND ";
						}
						$isInt = gettype($Condition->Value) == "integer";
						$conditionString .= $Condition->Column . $Condition->GetOperation() . ":c" . $count;
						$count++;
					}
				}
			}

			return $conditionString;
		}

		private function GetPdoParam($value)
		{
			$typeString = gettype($value);

			$ParamType = PDO::PARAM_STR;

			if($typeString == "integer")
			{
				$ParamType = PDO::PARAM_INT;
			}
			else if($typeString == "boolean")
			{
				$ParamType = PDO::PARAM_BOOL;
			}

			return $ParamType;
		}

	}

?>