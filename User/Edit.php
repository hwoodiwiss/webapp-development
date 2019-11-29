<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../core/utils.php';
require_once '../Model/User.php';
require_once '../Model/UserAccessLevel.php';
require_once '../core/html.php';

StartSession();
RequireAuth();


$user = SafeGetValue($_SESSION, 'User');

if ($user->AccessLevel->Name != 'Admin') 
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
	
	$gUser = $Users->Find($uid);
	if ($gUser === null) 
	{
		header('User not found', false, 404);
		exit();
	}

	$accessLevels = $UserAccessLevels->Select([]);

} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	$uid = ValidatePOSTValue('id');
	$email = ValidatePOSTValue('email');
	$fname = ValidatePOSTValue('fname');
	$lname = ValidatePOSTValue('lname');
	$jobTitle = ValidatePOSTValue('jtitle');
	$alevel = ValidatePOSTValue('alevels');

	$updateUser = $Users->Find($uid);
	$uAl = $updateUser->AccessLevel;
	if($updateUser != null)
	{
		$updateUser->Email = $email;
		$updateUser->FirstName = $fname;
		$updateUser->LastName = $lname;;
		$updateUser->AccessLevelId = $alevel;
		$updateUser->JobTitle = $jobTitle;

		$Users->UpdateObj($updateUser);
	}

	header('Location: /Users.php');
	exit();

}

HtmlHelper::$_Title = 'Edit User';

?>

<form action="/User/Edit.php" method="POST">
	<div class="row">
		<input name="id" type="hidden" value="<?php echo $gUser->Id ?>" />
		<label>Email</label>
		<?php $HTML->Input($gUser->Email, ['id' => 'email', 'class' => 'form-control', 'type' => 'email']); ?>
		<label>Firstname</label>
		<?php $HTML->Input($gUser->FirstName, ['id' => 'fname', 'class' => 'form-control']); ?>
		<label>Lastname</label>
		<?php $HTML->Input($gUser->LastName, ['id' => 'lname', 'class' => 'form-control']); ?>
		<label>Job Title</label>
		<?php $HTML->Input($gUser->JobTitle, ['id' => 'jtitle', 'class' => 'form-control']); ?>
		<label>Access Level</label>
		<?php $HTML->DropDownList($accessLevels, ['id' => 'alevels', 'class' => 'form-control'], 'Id', 'Name', $gUser->AccessLevelId); ?>
		<hr />
		<button class="btn btn-success" type="submit">Update</button>
		<a class="btn btn-warning" href="/Users.php">Cancel</a>
	</div>
</form>

<?php $HTML->Render() ?>