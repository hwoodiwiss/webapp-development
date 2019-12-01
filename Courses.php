<?php
require_once './Model/Course.php';
require_once './Model/Booking.php';
require_once './core/utils.php';
require_once './core/html.php';

HtmlHelper::$_Title = 'Courses';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

$Now = CurrentDateTime();

$UpcomingCourses = $Courses->Select([], [new DbCondition("StartDate", $Now, "gt"), new DbCondition("Active", true)]);

?>
<h3>Courses</h3>
<hr />
<table class="table table-light table-striped">
	<thead>
		<tr>
			<th>Title</th>
			<th>Start Date</th>
			<th>Duration</th>
			<th>Capacity</th>
			<th></th>
			<th></th>
		</tr>
	</thead>
	<tbody id="CourseTableBody">
		<?php foreach($UpcomingCourses as $CurrCourse): ?>
			<?php 
				$CourseBookings = $Bookings->Select(["Id", "UserId"], [new DbCondition("CourseId", $CurrCourse->Id)]);
				$UserBooking = Find($CourseBookings, function ($Value) use ($user) { return $Value->UserId == $user->Id; });
				$UserBooked = ($UserBooking != null);
				$NumBookings = count($CourseBookings);
				$IsFull = ($CurrCourse->Capacity - $NumBookings == 0)
			?>
			<tr>
				<td><?php echo htmlspecialchars($CurrCourse->Name) ?></td>
				<td><?php echo (new DateTime($CurrCourse->StartDate))->format("d/m/yy") ?></td>
				<td><?php echo $CurrCourse->Duration ?> Days</td>
				<td>
					<?php echo $CurrCourse->Capacity ?>
				</td>
				<td>
					<?php if($UserBooked): ?>
						<p class="text text-success">Booked!</p>
					<?php elseif($IsFull): ?>
						<p class="text text-danger">Course Full!</p>
					<?php else: ?>
						<form action="Enroll.php" method="POST">
							<input type="hidden" name="UserId" value="<?php echo $user->Id ?>">
							<input type="hidden" name="CourseId" value="<?php echo $CurrCourse->Id ?>">
							<button class="btn btn-success" type="submit">Enroll</button>
						</form>
					<?php endif; ?>
				</td>
				<td>
					<?php if($UserBooked): ?>
						<form action="/Admin/Booking/Delete.php" method="POST" onsubmit="return confirm('Are you sure you wish to cancel this booking?')">
							<input type="hidden" name="Id" value="<?php echo $UserBooking->Id ?>">
							<input type="hidden" name="Location" value="<?php echo urlencode($_SERVER['REQUEST_URI']) ?>">
							<button class="btn btn-danger" type="submit">Cancel</button>
						</form>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td collspan="6">
					<?php echo htmlspecialchars($CurrCourse->Description) ?>
				</td>
			</tr>
		<?php endforeach ?>
	</tbody>
</table>

<?php $HTML->Render() ?>
