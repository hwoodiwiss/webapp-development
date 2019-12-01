<?php

require_once './core/utils.php';
require_once './Model/User.php';
require_once './Model/Booking.php';
require_once './Model/Course.php';

StartSession();
RequireAuth();

include_once "./core/html.php";

$CurrUser = $_SESSION["User"];

$UserBookings = $Bookings->Select([],[new DbCondition("UserId", $CurrUser->Id)]);
$UpcomingCourses = Where($UserBookings, function($Booking){ return (new DateTime($Booking->Course->StartDate))->modify("+ " . $Booking->Course->Duration . " days")->getTimestamp() > (new DateTime('now'))->getTimestamp();});
$PastCourses = Where($UserBookings, function($Booking){ return (new DateTime('now'))->getTimestamp() > (new DateTime($Booking->Course->StartDate))->modify("+ " . $Booking->Course->Duration . " days")->getTimestamp();});

HtmlHelper::$_Title = "CourseMan";
?>

<div class="row">
<div class="col-sm-6">
	<div class="card text-dark">
		<div class="card-header">
			Upcoming Bookings
			<a class="btn btn-sm btn-dark float-right" href="/Courses.php">Manage</a>
		</div>
		<div class="card-body">
			<?php foreach($UpcomingCourses as $UpcomingCourse): ?>
				<div class="row">
					<div class="col-lg-4">
						<?php echo htmlspecialchars($UpcomingCourse->Course->Name) ?>
					</div>
					<div class="col-lg-4">
						<?php if(!$UpcomingCourse->Course->Active): ?>
							<p class="text text-danger">Cancelled</p>
						<?php else: ?>
							<?php echo "(" . (new DateTime($UpcomingCourse->Course->StartDate))->format("d/m/Y") . " - " . (new DateTime($UpcomingCourse->Course->StartDate))->modify("+ " . $UpcomingCourse->Course->Duration . " days")->format("d/m/Y") . ")"; ?>
						<?php endif; ?>
					</div>
				</div>
				<hr/>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<div class="col-sm-6">
	<div class="card text-dark">
		<div class="card-header">
			Course History
		</div>
		<div class="card-body">
			<?php foreach($PastCourses as $PastCourse): ?>
				<div class="row">
					<div class="col-lg-4">
						<?php echo htmlspecialchars($PastCourse->Course->Name) ?>
					</div>
					<div class="col-lg-4">
						<?php echo "(" . (new DateTime($PastCourse->Course->StartDate))->format("d/m/Y") . " - " . (new DateTime($PastCourse->Course->StartDate))->modify("+ " . $PastCourse->Course->Duration . " days")->format("d/m/Y") . ")"; ?>
					</div>
				</div>
				<hr/>
			<?php endforeach; ?>
		</div>
	</div>
</div>
</div>

<?php $HTML->Render() ?>