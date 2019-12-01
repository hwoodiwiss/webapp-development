<?php

require_once "../../core/utils.php";
require_once "../../Model/User.php";
require_once "../../Model/Course.php";
require_once "../../Model/Booking.php";

StartSession();
RequireAuth();

if(!IsAjax() || GetRequestMethod() != "GET")
{
	ErrorResponse(404);
}

$user = $_SESSION["User"];

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

$CourseId = SafeGetValue($_GET, "CourseId");

if($CourseId == null)
{
	ErrorResponse(404);
}

$CourseBookings = $Bookings->Select([], [new DbCondition("CourseId", $CourseId)]);

?>
<?php if(count($CourseBookings) > 0): ?>
<?php foreach($CourseBookings as $Booking): ?>
	<div class="row">
		<div class="col-3">
			<?php echo htmlspecialchars($Booking->User->FirstName) . " " . htmlspecialchars($Booking->User->LastName) ?>
		</div>
		<div class="col-3">
			Date Booked: <?php echo (new DateTime($Booking->Timestamp, new DateTimeZone('UTC')))->format('H:i d/m/Y') ?>
		</div>
		<div class="col-3">
			<form class="async" action="./Booking/Delete.php" method="POST" data-confirm="Are you sure you wish to delete this booking?" onsuccess="CM.Alert(data.Heading, data.Content, data.Type);ReloadContainingPanel(form);" onfail="CM.Alert(data.Heading, data.Content, data.Type);">
				<input type="hidden" name="Id" value="<?php echo $Booking->Id ?>">
				<button class="btn btn-danger float-right" type="submit">Delete</button>
			</form>
		</div>
	</div>
	<hr />
<?php endforeach; ?>
<?php else: ?>
	<p>No Bookings Found</p>
<?php endif; ?>