<?php 

	//polyfill for fastcgi instances that lack getallheaders()
	if (!function_exists('getallheaders')) 
	{
		function getallheaders() 
		{
			$headers = [];
			foreach ($_SERVER as $name => $value) 
			{
				if (substr($name, 0, 5) == 'HTTP_') 
				{
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}

			return $headers;
		}
	}

	function GenerateRandomString($length = 10) : string
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		
		return $randomString;
	}

	function CurrentDateTime() : string
	{
		$dt = new DateTime('now', new DateTimezone('Europe/London'));
		$currTime = $dt->format('Y-m-d H:i:s.u');
		return $currTime;
	}

	function SafeGetValue(array $dataArray, string $valName)
	{
		if(array_key_exists($valName, $dataArray))
		{
			return $dataArray[$valName];
		}
		else
		{
			return null;
		}

	}

	//"Liberated" from a blog post somewhere with very minor changes
	function CreateGUID() : string
	{
		if (function_exists('com_create_guid'))
		{
			return com_create_guid();
		}
		else
		{
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12);
			return $uuid;
		}
	}

	function ValidatePOSTValue(string $name, bool $required = false)
	{
		if(empty($name) && $required)
		{
			ErrorResponse(400);
			die($response->json());
		}

		if(empty($_POST[$name]))
		{
			if($required)
			{
				ErrorResponse(400);
				die($response->json());
			}
			else
			{
				return null;
			}
		}

		return $_POST[$name];
	}

	function GetPropertyAnnotations(string $ClassName, string $Property) : array
	{
		$propReflection = new ReflectionProperty($ClassName, $Property);
		$docComment = $propReflection->getDocComment();
		$Annotations = array();
		if($docComment != false)
		{
			$docComment = str_replace("*", "", $docComment);
			$docComment = str_replace("/", "", $docComment);
			$docDataAnnots = explode("@", $docComment);

			foreach($docDataAnnots as $value)
			{
				$value = trim($value);
				$keyVal = explode(" ", $value);
				if(count($keyVal) == 2)
				{
					$Annotations[$keyVal[0]] = $keyVal[1];
				}
			}
		}

		return $Annotations;
	}

	function Where(array $Collection, $Predicate)
	{
		$output = array();

		foreach($Collection as $Value)
		{
			if($Predicate($Value))
			{
				$output[] = $Value;
			}
		}

		return $output;
	}

	function Find(array $Collection, $Predicate)
	{
		foreach($Collection as $Value)
		{
			if($Predicate($Value)) return $Value;
		}

		return null;
	}

	//Safely verifies that the session is available
	function StartSession() : void
	{
		if(!isset($_SESSION))
		{
			session_start();
		}
	}

	function RequireAuth()
	{
		if(isset($_SESSION))
		{
			if(SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true)
			{
				header('Location: /login.php?location=' . urlencode($_SERVER['REQUEST_URI']));
				exit();
			}
		}
		else
		{
			header('Location: /login.php?location=' . urlencode($_SERVER['REQUEST_URI']));
			exit();
		}
	}

	function GetRequestMethod()
	{
		if(!array_key_exists("REQUEST_METHOD", $_SERVER))
		{
			return "";
		}

		return $_SERVER["REQUEST_METHOD"];
	}

	function ErrorResponse(int $ResponseCode)
	{
		http_response_code($ResponseCode);
		$responsePage = __DIR__ . '\\..\\' . $ResponseCode . '.php';
		if(file_exists($responsePage));
		{
			include_once $responsePage;
		}
		die();
	}

	function IsAjax()
	{
		return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
	}

class ResponseData extends ResponseMessage
{
	public $data;

	public function __construct(bool $success, string $message, array $data)
	{
		$this->success = $success;
		$this->message = $message;
		$this->data = $data;
	}

	public function json()
	{
		return json_encode(array(
			'success' => $this->success,
			'message' =>$this->message,
			'data' => $this->data
		));
	}
}

class ResponseMessage
{
	public $success;
	public $message;

	public function __construct(bool $success, string $message)
	{
		$this->success = $success;
		$this->message = $message;
	}

	public function json()
	{
		return json_encode(array(
			'success' => $this->success,
			'message' => $this->message
		));
	}
}
?>