<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once '../Model/Course.php';
require_once '../Model/Booking.php';
require_once '../core/utils.php';
require_once '../core/html.php';

HtmlHelper::$_Title = 'Courses';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

$Now = CurrentDateTime();

$UpcomingCourses = $Courses->Select([], [new DbCondition("Active", true)]);

?>
<h3>Courses</h3>
<hr />
<div class="col-12">
<table class="table table-dark table-striped">
	<thead>
		<tr>
			<th>Title</th>
			<th>Start Date</th>
			<th>Description</th>
			<th>Duration</th>
			<th>Capacity</th>
			<th>Currently Booked</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="CourseTableBody">
		<?php foreach($UpcomingCourses as $CurrCourse): ?>
			<?php 
				$CourseBookings = $Bookings->Select(["Id"], [new DbCondition("CourseId", $CurrCourse->Id)]);
				$NumBookings = count($CourseBookings);
				$CurrPercentage = ($NumBookings / $CurrCourse->Capacity) * 100;
			?>
			<tr data-toggle="collapse" data-target="#Details<?php echo $CurrCourse->Id ?>" role="button" aria-expanded="false" aria-controls="#Details<?php echo $CurrCourse->Id ?>" data-parent="#CourseTableBody">
				<td><?php echo $CurrCourse->Name ?></td>
				<td><?php echo (new DateTime($CurrCourse->StartDate))->format("d/m/yy") ?></td>
				<td><?php echo $CurrCourse->Description ?></td>
				<td><?php echo $CurrCourse->Duration ?> Days</td>
				<td><?php echo $CurrCourse->Capacity ?></td>
				<td>
					<div class="progress">
						<div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $CurrPercentage ?>%" aria-valuenow="<?php echo $CurrPercentage ?>" aria-valuemin="0" aria-valuemax="<?php echo $CurrCourse->Capacity ?>"><?php echo $NumBookings ?></div>
					</div>
				</td>
				<td><a class="btn btn-info" href="./Course/Edit.php?Id=<?php echo $CurrCourse->Id ?>">Edit</a></td>
				<td><form action="./Course/Delete.php" method="POST" onsubmit="return confirm('Are you sure you wish to delete this user?')"> <?php echo $HTML->Input($CurrCourse->Id, ["id" => "Id"], "hidden") ?> <button type="submit" class="btn btn-danger">Delete</button></form></td>
			</tr>
			<tr>
				<td class="unpadded" colspan="7">
					<div id="Details<?php echo $CurrCourse->Id ?>" class="collapse td-collapse"><?php echo $CurrCourse->Description ?></div>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>
<div>
	<a class="btn btn-success" href="./Course/Add.php">Add</a>
</div>
		</div>



<?php $HTML->Render() ?>
