<?php
require_once '../Model/Course.php';
require_once '../Model/Booking.php';
require_once '../core/utils.php';
require_once '../core/html.php';

HtmlHelper::$_Title = 'Admin: Courses';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

$Now = CurrentDateTime();

$UpcomingCourses = $Courses->Select([], [new DbCondition("StartDate", $Now, "ge"), new DbCondition("Active", true)]);
$HistoricalCourses = $Courses->Select([], [new DbCondition("StartDate", $Now, "lt"), new DbCondition("Active", true)]);

?>
<h3>Courses</h3>
<p>Click a row to see it's bookings</p>
<hr />
<div class="row">
	<h4>Upcoming Courses</h4>
	<table class="table table-light table-hover table-striped">
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
			<tr data-toggle="collapse" data-target="#Details<?php echo $CurrCourse->Id ?>" role="button"
				aria-expanded="false" aria-controls="#Details<?php echo $CurrCourse->Id ?>"
				data-parent="#CourseTableBody">
				<td><?php echo htmlspecialchars($CurrCourse->Name) ?></td>
				<td><?php echo (new DateTime($CurrCourse->StartDate))->format("d/m/Y") ?></td>
				<td><?php echo htmlspecialchars($CurrCourse->Description) ?></td>
				<td><?php echo $CurrCourse->Duration ?> Days</td>
				<td><?php echo $CurrCourse->Capacity ?></td>
				<td>
					<div class="progress">
						<div class="progress-bar bg-success" role="progressbar"
							style="width: <?php echo $CurrPercentage ?>%" aria-valuenow="<?php echo $CurrPercentage ?>"
							aria-valuemin="0" aria-valuemax="<?php echo $CurrCourse->Capacity ?>">
							<?php echo $NumBookings ?></div>
					</div>
				</td>
				<td><a class="btn btn-info" href="./Course/Edit.php?Id=<?php echo $CurrCourse->Id ?>">Edit</a></td>
				<td>
					<form action="./Course/Delete.php" method="POST"
						onsubmit="return confirm('Are you sure you wish to delete this course?')">
						<?php echo $HTML->Input($CurrCourse->Id, ["id" => "Id"], "hidden") ?> <button type="submit"
							class="btn btn-danger">Delete</button></form>
				</td>
			</tr>
			<tr>
				<td class="unpadded" colspan="8">
					<div id="Details<?php echo $CurrCourse->Id ?>" class="collapse td-collapse async-panel"
						data-target="./Course/CourseBookings.php" data-id='{"CourseId":<?php echo $CurrCourse->Id ?>}'>

					</div>
				</td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<div>
		<a class="btn btn-success" href="./Course/Add.php">Add</a>
	</div>
</div>
<div class="row">
	<h4>Historical Courses</h4>
	<table class="table table-light">
		<thead>
			<tr>
				<th>Title</th>
				<th>Start Date</th>
				<th>Description</th>
				<th>Duration</th>
				<th>Capacity</th>
				<th>Attendance</th>
			</tr>
		</thead>
		<tbody id="CourseTableBody">
			<?php foreach($HistoricalCourses as $CurrCourse): ?>
			<?php 
				$CourseBookings = $Bookings->Select(["Id"], [new DbCondition("CourseId", $CurrCourse->Id)]);
				$NumBookings = count($CourseBookings);
			?>
			<tr data-toggle="collapse" data-target="#Details<?php echo $CurrCourse->Id ?>" role="button"
				aria-expanded="false" aria-controls="#Details<?php echo $CurrCourse->Id ?>"
				data-parent="#CourseTableBody">
				<td><?php echo htmlspecialchars($CurrCourse->Name) ?></td>
				<td><?php echo (new DateTime($CurrCourse->StartDate))->format("d/m/Y") ?></td>
				<td><?php echo htmlspecialchars($CurrCourse->Description) ?></td>
				<td><?php echo $CurrCourse->Duration ?> Days</td>
				<td><?php echo $CurrCourse->Capacity ?></td>
				<td><?php echo $NumBookings ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
</div>
<?php $HTML->Render() ?>
