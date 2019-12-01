<?php
require_once '../../Model/User.php';
require_once '../../Model/UserAccessLevel.php';
require_once '../../core/utils.php';
require_once '../../core/html.php';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$Email = ValidatePOSTValue('Email');
	$PasswordSalt = hash('sha512', GenerateRandomString(128), false);
	$PasswordHash = hash('sha512', ValidatePOSTValue('Password') . $PasswordSalt, false);
	$FirstName = ValidatePOSTValue('FirstName');
	$LastName = ValidatePOSTValue('LastName');
	$AccessLevel = ValidatePOSTValue('AccessLevel');
	$JobTitle = ValidatePOSTValue('JobTitle');
	$Timestamp = CurrentDateTime();

	$UniqueCheck = $Users->Select(['Id'], [new DbCondition('Email', $Email, 'like')]);

	if(count($UniqueCheck) > 0)
	{
		header('Location: ./Add.php?err=NotUnique');
		exit();
	}

	$NewUser = new User();
	$NewUser->Email = $Email;
	$NewUser->PassHash = $PasswordHash;
	$NewUser->PassSalt = $PasswordSalt;
	$NewUser->FirstName = $FirstName;
	$NewUser->LastName = $LastName;;
	$NewUser->AccessLevelId = $AccessLevel;
	$NewUser->JobTitle = $JobTitle;
	$NewUser->Active = true;
	$NewUser->Timestamp = $Timestamp;

	$Users->InsertObj($NewUser);

	header('Location: ../Users.php');
	exit();
}
$accessLevels = array();
$Err = "";
if($_SERVER['REQUEST_METHOD'] === 'GET')
{
	$accessLevels = $UserAccessLevels->Select([]);
	$Err = SafeGetValue($_GET, "err");
}

HtmlHelper::$_Title = 'Admin: Add User';

?>

<?php if($Err == "NotUnique"): ?>
	<script>
		$(document).ready(function(){
			CM.Alert("Email not unique!", "A user already exists with the provided email address!", "danger");
		});
	</script>
<?php endif; ?>
<h3>New User</h3>
<hr />
<form action="./Add.php" method="POST">
<div class="form-row">
		<div class="form-group col-6">
			<label>Email</label>
			<?php $HTML->Input("", ['id' => 'Email', 'class' => 'form-control', 'type' => 'email', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-6">
			<label>Password</label>
			<?php $HTML->Input("", ['id' => 'Password', 'class' => 'form-control', 'type' => 'password', "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Firstname</label>
			<?php $HTML->Input("", ['id' => 'FirstName', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Lastname</label>
			<?php $HTML->Input("", ['id' => 'LastName', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-12">
			<label>Job Title</label>
			<?php $HTML->Input("", ['id' => 'JobTitle', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-12">
			<label>Access Level</label>
			<?php $HTML->DropDownList($accessLevels, ['id' => 'AccessLevel', 'class' => 'form-control', "required" => ""], 'Id', 'Name'); ?>
		</div>
		<hr />
	</div>
	<button class="btn btn-success" type="submit">Save</button>
	<a class="btn btn-warning" href="../Users.php">Cancel</a>
</form>

<?php $HTML->Render() ?>