<?php
require_once '../../core/utils.php';
require_once '../../Model/User.php';
require_once '../../Model/UserAccessLevel.php';
require_once '../../core/html.php';

StartSession();
RequireAuth();


$user = SafeGetValue($_SESSION, 'User');

if ($user->AccessLevel->Name != 'Admin') 
{
	ErrorResponse(401);
	exit();
}

$gUser = null;
$accessLevels = null;
if ($_SERVER['REQUEST_METHOD'] === 'GET') 
{
	$uid = SafeGetValue($_GET, 'Id');
	if ($uid === null) 
	{
		ErrorResponse(404);
		exit();
	}
	
	$gUser = $Users->Find($uid);
	if ($gUser === null) 
	{
		ErrorResponse(404);
		exit();
	}

	$accessLevels = $UserAccessLevels->Select([]);

} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	$Id = ValidatePOSTValue('Id');
	$Email = ValidatePOSTValue('Email');
	$FirstName = ValidatePOSTValue('FirstName');
	$LastName = ValidatePOSTValue('LastName');
	$JobTitle = ValidatePOSTValue('JobTitle');
	$AccessLevel = ValidatePOSTValue('AccessLevel');

	$updateUser = $Users->Find($Id);

	if($updateUser != null)
	{
		$updateUser->Email = $Email;
		$updateUser->FirstName = $FirstName;
		$updateUser->LastName = $LastName;;
		$updateUser->AccessLevelId = $AccessLevel;
		$updateUser->JobTitle = $JobTitle;

		$Users->UpdateObj($updateUser);
	}

	header('Location: ../Users.php');
	exit();

}

HtmlHelper::$_Title = 'Admin: Edit User';

?>

<h3>Edit User: <?php echo htmlspecialchars($gUser->LastName) ?>, <?php echo htmlspecialchars($gUser->FirstName) ?></h3>
<hr />

<form action="./Edit.php" method="POST">
	<div class="form-row">
		<input name="Id" type="hidden" value="<?php echo $gUser->Id ?>" />
		<div class="form-group col-12">
			<label>Email</label>
			<?php $HTML->Input($gUser->Email, ['id' => 'Email', 'class' => 'form-control', 'type' => 'email', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Firstname</label>
			<?php $HTML->Input($gUser->FirstName, ['id' => 'FirstName', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Lastname</label>
			<?php $HTML->Input($gUser->LastName, ['id' => 'LastName', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-12">
			<label>Job Title</label>
			<?php $HTML->Input($gUser->JobTitle, ['id' => 'JobTitle', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-12">
			<label>Access Level</label>
			<?php $HTML->DropDownList($accessLevels, ['id' => 'AccessLevel', 'class' => 'form-control', "required" => ""], 'Id', 'Name', $gUser->AccessLevelId); ?>
		</div>
		<hr />
	</div>
	<button class="btn btn-success" type="submit">Update</button>
	<a class="btn btn-warning" href="../Users.php">Cancel</a>
</form>

<?php $HTML->Render() ?>