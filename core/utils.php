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
		$Chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$NumChars = strlen($Chars);
		$randomString = '';
		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $Chars[rand(0, $NumChars - 1)];
		}
		
		return $randomString;
	}

	//Gets the current date and time in UTC
	function CurrentDateTime() : string
	{
		$dt = new DateTime('now', new DateTimezone('UTC'));
		$currTime = $dt->format('Y-m-d H:i:s.u');
		return $currTime;
	}

	//Safeley gets a value from an array if it exists, or null if it doesn't
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

	//Safely gets a value from $_POST, can be made to respond with err 400
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

	//Gets an assoc array of annotations on a class property, parsed from documentation comments
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

	//Returns a collection of items from the passed in collection that meet the requirements of the predicate
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

	//Returns the first item from the passed in collection that meet the requirements of the predicate
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
			ini_set('session.cookie_httponly', 1);
			ini_set('session.use_only_cookies', 1);
			//Allows insecure cookie for localhost non https connections
			if($_SERVER['HTTP_HOST'] != "localhost") ini_set('session.cookie_secure', 1);

			session_start();
		}
	}

	//Checks if the user is authenticated, and if not, redirects to the login page
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

	//Gets the method of the current request
	function GetRequestMethod()
	{
		if(!array_key_exists("REQUEST_METHOD", $_SERVER))
		{
			return "";
		}

		return $_SERVER["REQUEST_METHOD"];
	}

	//Sets the response code, and if available, outputs a custom Error page
	function ErrorResponse(int $ResponseCode)
	{
		http_response_code($ResponseCode);
		$responsePage = __DIR__ . '/../' . $ResponseCode . '.php';
		if(file_exists($responsePage) && !IsAjax());
		{
			include_once $responsePage;
		}
		die();
	}

	//Returns true if the current request was made asynchronously
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

	public function __toString()
	{
		return $this->json();
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