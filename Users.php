<?php

require_once './Model/User.php';
require_once './Model/UserAccessLevel.php';
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

$users = $Users->Select([], [new DbCondition("Active", true)]);
$accessLevels = $UserAccessLevels->Select([]);

$userCount = count($users);

?>
<h3>Users</h3>
<hr />
<table class="table table-dark table-striped">
	<thead>
		<tr>
			<th>Email</th>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Accesslevel</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="UserTableBody">
		<?php foreach($users as $curr): ?>
			<tr data-toggle="collapse" data-target="#Details<?php echo $curr->Id ?>" role="button" aria-expanded="false" aria-controls="#Details<?php echo $curr->Id ?>" data-parent="#UserTableBody">
				<td><?php echo $curr->Email ?></td>
				<td><?php echo $curr->FirstName ?></td>
				<td><?php echo $curr->LastName ?></td>
				<td><?php echo $curr->AccessLevel->Name ?></td>
				<td><a class="btn btn-info" href="/User/Edit.php?Id=<?php echo $curr->Id ?>">Edit</a></td>
				<td><form action="/User/Delete.php" method="POST" onsubmit="return confirm('Are you sure you wish to delete this user?')"> <?php echo $HTML->Input($curr->Id, ["id" => "Id"], "hidden") ?> <button type="submit" class="btn btn-danger">Delete</button></form></td>
			</tr>
			<tr>
				<td class="unpadded" colspan="7">
					<div id="Details<?php echo $curr->Id ?>" class="collapse td-collapse">Moar Details</div>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<div>
	<a class="btn btn-success" href="/User/Add.php">Add</a>
</div>

<?php $HTML->Render() ?>
