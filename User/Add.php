<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../Model/User.php';
require_once '../core/utils.php';
require_once '../core/database.php';
require_once '../core/html.php';

session_start();

if(SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true)
{
	header('Location: /login.php');
}

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel != 'Admin')
{
	header('HTTP/1.0 401 Not Authorised', 401);
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$email = ValidatePOSTValue('email');
	$passSalt = hash('sha512', GenerateRandomString(128), false);
	$passHash = hash('sha512', ValidatePOSTValue('password') . $passSalt, false);
	$fname = ValidatePOSTValue('fname');
	$lname = ValidatePOSTValue('lname');
	$alevel = ValidatePOSTValue('alevel');
	$ts = CurrentDateTime();

	$db = new DB();
	$stmt = $db->prepare('INSERT INTO Users (Email, PassHash, PassSalt, FirstName, LastName, AccessLevel, Timestamp) VALUES (:email, :pass, :salt, :fname, :lname, :access, :timestamp)');
	$stmt->bindParam(':email', $email, PDO::PARAM_STR);
	$stmt->bindParam(':pass', $passHash, PDO::PARAM_STR);
	$stmt->bindParam(':salt', $passSalt, PDO::PARAM_STR);
	$stmt->bindParam(':fname', $fname, PDO::PARAM_STR);
	$stmt->bindParam(':lname', $lname, PDO::PARAM_STR);
	$stmt->bindParam(':access', $alevel, PDO::PARAM_STR);
	$stmt->bindParam(':timestamp', $ts, PDO::PARAM_STR);

	if(!$stmt->execute())
	{
		die('An error occured: ' . $stmt->errorInfo()[2]);
	}

	header('Location: /users.php');
}
$accessLevels = array();
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$usrLevel = new class { public $id = 'User'; public $value = 'User';};
	$susrLevel = new class { public $id = 'SuperUser'; public $value = 'SuperUser';};
	$admLevel = new class { public $id = 'Admin'; public $value = 'Admin';};
	$accessLevels = array($usrLevel, $susrLevel, $admLevel);
}

HtmlHelper::$_Title = 'Add User';

?>

<form action="/User/Add.php" method="POST">
	<div class="row">
		<label>Email</label>
		<?php $HTML->TextBox("", ['id' => 'email', 'class' => 'form-control', 'type' => 'email']); ?>
		<label>Password</label>
		<?php $HTML->TextBox("", ['id' => 'password', 'class' => 'form-control', 'type' => 'password']); ?>
		<label>FirstName</label>
		<?php $HTML->TextBox("", ['id' => 'fname', 'class' => 'form-control']); ?>
		<label>LastName</label>
		<?php $HTML->TextBox("", ['id' => 'lname', 'class' => 'form-control']); ?>
		<label>Access Level</label>
		<?php $HTML->DropDownList($accessLevels, ['id' => 'alevels', 'class' => 'form-control']); ?>
		<hr />
		<button class="btn btn-success" type="submit">Save</button>
	</div>
</form>

<?php $HTML->Render() ?>