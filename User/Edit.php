<?php

require_once 'Model.php';
require_once '../core/database.php';
require_once '../core/html.php';

session_start();

if (SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true) {
	header('Location: /login.php');
	exit();
}

$user = SafeGetValue($_SESSION, 'User');

if ($user->AccessLevel != 'Admin') 
{
	header('HTTP/1.0 401 Not Authorised', 401);
	exit();
}

$gUser = null;
$accessLevels = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') 
{
	$uid = SafeGetValue($_GET, 'Id');
	if ($uid === null) 
	{
		header('User not found', false, 404);
		exit();
	}

	$db = new DB();
	$stmt = $db->prepare('SELECT * FROM Users WHERE Id = :id');
	$stmt->bindParam(':id', $uid, PDO::PARAM_INT);

	if (!$stmt->execute()) {
		die('An error occured:' . $stmt->errorInfo()[2]);
	}

	$data = $stmt->fetchAll();
	
	if (count($data) !== 1) 
	{
	   die('More than one record found for the provided id, this should not be possible.');
	}

	$gUser = new User($data[0]);

	$usrLevel = new class { public $id = 'User'; public $value = 'User';};
	$susrLevel = new class { public $id = 'SuperUser'; public $value = 'SuperUser';};
	$admLevel = new class { public $id = 'Admin'; public $value = 'Admin';};
	$accessLevels = array($usrLevel, $susrLevel, $admLevel);
} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	$uid = ValidatePOSTValue('id');
	$email = ValidatePOSTValue('email');
	$passSalt = hash('sha512', GenerateRandomString(128), false);
	$passHash = hash('sha512', ValidatePOSTValue('password') . $passSalt, false);
	$fname = ValidatePOSTValue('fname');
	$lname = ValidatePOSTValue('lname');
	$alevel = ValidatePOSTValue('alevel');
	$ts = CurrentDateTime();


	$db = new DB();
	$stmt = $db->prepare('SELECT * FROM Users WHERE Id = :id');
	$stmt->bindParam(':id', $uid, PDO::PARAM_INT);

	if (!$stmt->execute()) {
		die('An error occured:' . $stmt->errorInfo()[2]);
	}

	$data = $stmt->fetchAll();
	
	if (count($data) !== 1) 
	{
	   die('More than one record found for the provided id, this should not be possible.');
	}

}

HtmlHelper::$_Title = 'Edit User';

?>

<form action="/User/Edit.php" method="POST">
	<div class="row">
		<input name="id" type="hidden" value="<?php echo $gUser->Id ?>" />
		<label>Email</label>
		<input class="form-control" type="email" name="email" value="<?php echo $gUser->Email ?>" />
		<label>Password</label>
		<input class="form-control" type="password" name="password" />
		<label>FirstName</label>
		<input class="form-control" type="text" name="fname" value="<?php echo $gUser->FirstName ?>" />
		<label>LastName</label>
		<input class="form-control" type="text" name="lname" value="<?php echo $gUser->LastName ?>" />
		<label>Access Level</label>
		<?php $HTML->DropDownListFor($accessLevels, 'alevels', 'form-control', 'id', 'value', $gUser->AccessLevel); ?>
		<hr />
		<button class="btn btn-success" type="submit">Update</button>
		<a class="btn btn-warning" href="/Users.php">Cancel</a>
	</div>
</form>

<?php $HTML->Render() ?>