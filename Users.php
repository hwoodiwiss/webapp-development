<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once './core/utils.php';
require_once './core/database.php';
require_once './User/Model.php';
require_once './core/html.php';

HtmlHelper::$_Title = 'Users';

session_start();

if(SafeGetValue($_SESSION, 'auth') == null || $_SESSION['auth'] != true)
{
	header('Location: /login.php');
	exit();
}

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel != 'Admin')
{
	header('HTTP/1.0 401 Not Authorised', 401);
}

$db = new DB();
$stmt = $db->prepare('SELECT * FROM Users');

if(!$stmt->execute())
{
	die('An error occured: ' . $stmt->errorInfo()[2]);
}

$users = $stmt->fetchAll();
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
				$curr = new User($users[$i]);
				echo('<tr><td>'.$curr->Email.'</td><td>'.$curr->FirstName.'</td><td>'.$curr->LastName.'</td><td>'.$curr->AccessLevel.'</td><td><a class="btn btn-info" href="/User/Edit.php?Id='.$curr->Id.'">Edit</a></td></tr>');
			}
		?>
	</tbody>
</table>

<div>
	<a class="btn btn-success" href="/User/Add.php">Add</a>
</div>

<?php $HTML->Render() ?>
