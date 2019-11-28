<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../Model/User.php';
require_once '../Model/UserAccessLevel.php';
require_once '../core/utils.php';
require_once '../core/html.php';

SessionStart();
RequireAuth();

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
	$alevel = ValidatePOSTValue('alevels');
	$jobTitle = ValidatePOSTValue('jtitle');
	$ts = CurrentDateTime();

	$NewUser = new User();
	$NewUser->Email = $email;
	$NewUser->PassHash = $passHash;
	$NewUser->PassSalt = $passSalt;
	$NewUser->FirstName = $fname;
	$NewUser->LastName = $lname;;
	$NewUser->AccessLevelId = $alevel;
	$NewUser->JobTitle = $jobTitle;
	$NewUser->Active = true;
	$NewUser->Timestamp = $ts;

	$Users->InsertObj($NewUser);

	header('Location: /Users.php');
}
$accessLevels = array();
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$accessLevels = $UserAccessLevels->Select([]);
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
		<label>Job Title</label>
		<?php $HTML->TextBox("", ['id' => 'jtitle', 'class' => 'form-control']); ?>
		<label>Access Level</label>
		<?php $HTML->DropDownList($accessLevels, ['id' => 'alevels', 'class' => 'form-control'], 'Id', 'Name'); ?>
		<hr />
		<button class="btn btn-success" type="submit">Save</button>
	</div>
</form>

<?php $HTML->Render() ?>