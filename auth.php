<?php
require_once './core/utils.php';
require_once './core/database.php';
require_once './Model/User.php';

if(!$_SERVER['REQUEST_METHOD'] === 'POST')
{
	ErrorResponse(404);
	die();
}

$email = $_POST['Email'];
$password = $_POST['Password'];
$location = SafeGetValue($_POST, 'location');

$FoundUsers = $Users->Select([], [new DbCondition("Email", $email)]);

if(count($FoundUsers) !== 1)
{
	//Allows the response to either be JSON or a redirect
	if(IsAjax())
	{
		$Response = new ResponseData(false, '', ['Heading' => 'Username or Password!', 'Content' => 'The username or password was incorrect!', 'Type' => 'danger']);
		echo $Response->json();
		exit();
	}
	else
	{
		header("Location: /login.php?err=e01" . ($location != null ? "&location=" . $location : ""));
		exit();
	}
}

$User = $FoundUsers[0];

$inputPassHash = hash('sha512', $password . $User->PassSalt, FALSE);

if($User->PassHash != $inputPassHash)
{
	//Allows the response to either be JSON or a redirect
	if(IsAjax())
	{
		$Response = new ResponseData(false, '', ['Heading' => 'Username or Password!', 'Content' => 'The username or password was incorrect!', 'Type' => 'danger']);
		echo $Response->json();
		exit();
	}
	else
	{
		header("Location: /login.php?err=e01" . ($location != null ? "&location=" . $location : ""));
		exit();
	}
}

StartSession();

$_SESSION['auth'] = true;
$_SESSION['User'] = $User;

if($location != null)
{
	if(IsAjax())
	{
		$Response = new ResponseData(true, '', ['Location' => urldecode($location)]);
		echo $Response->json();
	}
	else
	{
		header("Location: " . urldecode($location));
	}
	exit();

}

if(IsAjax())
{
	$Response = new ResponseData(true, '', ['Location' => '/index.php']);
	echo $Response->json();
}
else
{
	header("Location: /index.php"); 
}
exit();

?>