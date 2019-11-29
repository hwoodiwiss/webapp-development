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
		
	}

	//Class to derive DB Data models from
	class DbData
	{
		//Uses data in assoc array from DB to construct the properties, setting correct types where necessary
		public function __construct(array $dbObject = null)
		{
			if($dbObject != null)
			{
				$class = get_class($this);
				$props = get_object_vars($this);
				foreach($props as $key => $value)
				{
					$annotations = GetPropertyAnnotations($class, $key);
					$type = SafeGetValue($annotations, "val");
					$this->$key = SafeGetValue($dbObject, $key);
					if($type != null)
					{
						if(class_exists($type))
						{
							$this->$key = new $type($value);
						}
						else
						{
							if(!settype($this->$key, $type)) throw new Exception("Invalid conversion from string to " . $type);
						}
					}
				}
			}
		}

		//Using __get magic function to create virtual navigation properties to other DbData classes
		public function __get($Name)
		{
			$ClassName = get_class($this);
			$Props = get_object_vars($this);

			foreach($Props as $Prop => $Val)
			{
				$annotations = GetPropertyAnnotations($ClassName, $Prop);
				$ForeignAlias = SafeGetValue($annotations, "fkey-alias");
				if($ForeignAlias == $Name)
				{
					$ForeignTable = SafeGetValue($annotations, "fkey-table");
					if($ForeignTable != null)
					{
						$ForeignType = substr($ForeignTable, 0 , -1);
						return (new DbHelper($ForeignType))->Find($Val);
					}
				}
			}

			throw new Exception("Invalid navigation property: " . $Name . "!");
		}

		//Coerces any properties with a var type set in annotations to specified type
		public function CoerceTypes()
		{
			$class = get_class($this);
			$props = get_object_vars($this);
			foreach($props as $key => $value)
			{
				$annotations = GetPropertyAnnotations($class, $key);
				$type = SafeGetValue($annotations, "var");
				if($type != null)
				{
					if(!settype($this->$key, $type)) throw new Exception("Invalid conversion from string to " . $type);
				}
			}
		}

		//Gets a partially full copy of the object
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
			$outObj = new $typeName();
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

	//Helper class for easily constructing basic SQL requests and retuning serialized results
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

		//Inserts a new object of the type that this DbHelper is built for
		public function InsertObj($object)
		{
			//Make sure that the 
			if(get_class($object) != $this->TypeName) throw new Exception("Invalid object type for this helper!");

			$object->CoerceTypes();

			$objVals = get_object_vars($object);
			$columnVals = array();
			foreach($objVals as $Var => $Val)
			{
				if($Val != null && $Var != "Id")
				{
					$columnVals[$Var] = $Val;
				}
			}

			$this->Insert($columnVals);
		}

		//Inserts new row into the table with specified values
		public function Insert(array $ColumnVals)
		{
			$insertString = "INSERT INTO " . $this->TableName . " (" . $this->BuildInsertColsString($ColumnVals)
			 . ") VALUES (" . $this->BuildInsertValuesString($ColumnVals) . ");";

			$db = new Db();
			$stmt = $db->prepare($insertString);

			$count = 0;
			foreach($ColumnVals as $Column => $Val)
			{
				$paramStr = ":v" . $count;
				if(!$stmt->bindValue($paramStr, $Val, $this->GetPdoParam($Val)))
				{
					var_dump($stmt);
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

			$object->CoerceTypes();

			$objVals = get_object_vars($object);
			$columnVals = array();
			foreach($objVals as $Var => $Val)
			{
				$columnVals[$Var] = $Val;
			}
			$this->Update($columnVals, [new DbCondition("Id", $object->Id)]);
		}

		public function Update(array $ColumnVals, array $Conditions = [])
		{
			$updateString = "UPDATE " . $this->TableName . " SET " 
			. $this->BuildPreparedValuesString($ColumnVals) . $this->BuildConditionString($Conditions) . ";";
			$db = new Db();
			$stmt = $db->prepare($updateString);

			$count = 0;
			foreach($ColumnVals as $Column => $Val)
			{
				$paramStr = ":v" . $count;
				if(!$stmt->bindValue($paramStr, $Val, $this->GetPdoParam($Val)))
				{
					throw new Exception("Failed to bind parameter " . $paramStr . " Value: " . $Val);
				}
				$count++;
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

		public function Delete($Id)
		{
			$deleteString = "DELETE FROM " . $this->TableName . " WHERE Id = :v0";

			$db = new Db();
			$stmt = $db->prepare($deleteString);
			$stmt->bindValue(":v0", $Id, $this->GetPdoParam($Id));

			if(!$stmt->execute())
			{
				throw new Exception("An error occured updating the database. Error info: " . $stmt->errorInfo()[2]);
			}
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

		private function BuildInsertColsString(array $Columns) : string
		{
			$colsString = "";
			if($Columns != [])
			{
				$count = 0;
				foreach($Columns as $Key => $Val)
				{
					if($count != 0)
					{
						$colsString .= ", ";
					}
					
					$colsString .= $Key;
					$count++;
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
			$valuesString = "";
			if($Values != [])
			{
				$count = 0;
				foreach($Values as $Column => $Value)
				{

					if($count > 0)
					{
						$valuesString .= ", ";
					}

					$valuesString .= $Column . " = :v" . $count;
					$count++;
				}
			}

			return $valuesString;
		}
		
		private function BuildInsertValuesString(array $Values) : string
		{
			$valuesString = "";
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
						$valuesString .= ":v" . $count;
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