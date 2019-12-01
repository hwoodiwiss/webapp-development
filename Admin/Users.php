<?php

require_once '../Model/User.php';
require_once '../Model/UserAccessLevel.php';
require_once '../core/html.php';

HtmlHelper::$_Title = 'Admin: Users';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

$users = $Users->Select([], [new DbCondition("Active", true)]);
$accessLevels = $UserAccessLevels->Select([]);

$userCount = count($users);

?>
<h3>Users</h3>
<hr />
<table class="table table-light table-striped">
	<thead>
		<tr>
			<th>Email</th>
			<th>Firstname</th>
			<th>Lastname</th>
			<th>Job Title</th>
			<th>Accesslevel</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="UserTableBody">
		<?php foreach($users as $curr): ?>
			<tr>
				<td><?php echo htmlspecialchars($curr->Email) ?></td>
				<td><?php echo htmlspecialchars($curr->FirstName) ?></td>
				<td><?php echo htmlspecialchars($curr->LastName) ?></td>
				<td><?php echo htmlspecialchars($curr->JobTitle) ?></td>
				<td><?php echo $curr->AccessLevel->Name ?></td>
				<td><a class="btn btn-info" href="./User/Edit.php?Id=<?php echo $curr->Id ?>">Edit</a></td>
				<td>
					<?php if($curr->Id != $user->Id): ?>
					<form action="./User/Delete.php" method="POST" onsubmit="return confirm('Are you sure you wish to delete this user?')"> <?php echo $HTML->Input($curr->Id, ["id" => "Id"], "hidden") ?> <button type="submit" class="btn btn-danger">Delete</button></form>
					<?php endif; ?>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<div>
	<a class="btn btn-success" href="./User/Add.php">Add</a>
</div>

<?php $HTML->Render() ?>
