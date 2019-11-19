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
		if(empty($name) && $reqired)
		{
			http_response_code(400);
			$response = new ResponseMessage(false, 'Invalid data provided');
			die($response->json());
		}

		if(empty($_POST[$name]))
		{
			http_response_code(400);
			$response = new ResponseMessage(false, 'Invalid data provided: ' . $name);
			die($response->json());
		}

		return $_POST[$name];
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