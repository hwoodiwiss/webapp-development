<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once './Model/User.php';
require_once './core/utils.php';
require_once './core/database.php';
require_once './core/html.php';

HtmlHelper::$_Title = 'Users';

session_start();

if(SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true)
{
	header('Location: /login.php');
	exit();
}

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevelId != 'Admin')
{
	header('HTTP/1.0 401 Not Authorised', 401);
}

$ShowInactive = SafeGetValue($_GET, "Active");

$users = $Users->Select([], [Active => true]);

$userCount = count($users);

?>

<table class="table">
	<thead>
		<tr>
			<th>Email</th>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Accesslevel</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
			for($i = 0; $i < $userCount; $i++)
			{
				$curr = $users[$i];
				echo('<tr><td>'.$curr->Email.'</td><td>'.$curr->FirstName.'</td><td>'.$curr->LastName.'</td><td>'.$curr->AccessLevelId.'</td><td><a class="btn btn-info" href="/User/Edit.php?Id='.$curr->Id.'">Edit</a></td></tr>');
			}
		?>
	</tbody>
</table>

<div>
	<a class="btn btn-success" href="/User/Add.php">Add</a>
</div>

<?php $HTML->Render() ?>
