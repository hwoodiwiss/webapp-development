<?php
ini_set('display_errors', 1);

require_once '../../core/utils.php';
require_once '../../Model/User.php';
require_once '../../Model/Course.php';
require_once '../../Model/Booking.php';
require_once '../../core/html.php';

StartSession();
RequireAuth();


$user = SafeGetValue($_SESSION, 'User');

if ($user->AccessLevel->Name != 'Admin') 
{
	ErrorResponse(401);
	exit();
}

$gCourse = null;
$NumBookings = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET') 
{
	$Id = SafeGetValue($_GET, 'Id');
	if ($Id === null) 
	{
		ErrorResponse(404);
		exit();
	}
	
	$gCourse = $Courses->Find($Id);
	if ($gCourse === null) 
	{
		ErrorResponse(404);
		exit();
	}

	$CourseBookings = $Bookings->Select(["Id"], [new DbCondition("CourseId", $gCourse->Id)]);
	$NumBookings = count($CourseBookings);

} 
else if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	$Id = ValidatePOSTValue('Id', true);
	$Title = ValidatePOSTValue('Title');
	$StartDate = ValidatePOSTValue('StartDate');
	$Description = ValidatePOSTValue('Description');
	$Duration = ValidatePOSTValue('Duration');
	$Capacity = ValidatePOSTValue('Capacity');

	$updateCourse = $Courses->Find($Id);

	if($updateCourse != null)
	{
		$updateCourse->Name = $Title;
		$updateCourse->StartDate = $StartDate;
		$updateCourse->Description = $Description;
		$updateCourse->Duration = $Duration;
		$updateCourse->Capacity = $Capacity;

		$Courses->UpdateObj($updateCourse);
	}

	header('Location: ../Courses.php');
	exit();

}

HtmlHelper::$_Title = 'Admin: Edit Course';

?>

<h3>Edit Course</h3>
<hr />
<form action="./Edit.php" method="POST">
	<div class="form-row">
		<input type="hidden" name="Id" value="<?php echo $gCourse->Id ?>" >
		<div class="form-group col-6">
			<label>Title</label>
			<?php $HTML->Input($gCourse->Name, ['id' => 'Title', 'placeholder' => 'Course Title', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-6">
			<label>Start Date</label>
			<?php $HTML->Input($gCourse->StartDate, ['id' => 'StartDate', 'placeholder' => 'Course Start Date', 'class' => 'form-control', "required" => "", "min" => (new DateTime('now', new DateTimeZone('Europe/London')))->format('Y-m-d')], 'date'); ?>
		</div>
		<div class="form-group col-12">
			<label>Description</label>
			<?php $HTML->Input($gCourse->Description, ['id' => 'Description', 'placeholder' => 'Course Description', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Duration</label>
			<?php $HTML->Input($gCourse->Duration, ['id' => 'Duration', 'placeholder' => 'Course Duration (Days)', 'class' => 'form-control', "required" => "", "min" => "1"], 'number'); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Capacity</label>
			<?php $HTML->Input($gCourse->Capacity, ['id' => 'Capacity', 'placeholder' => 'Course Capacity', 'class' => 'form-control', 'min' => $NumBookings, "required" => "", "min" => "1"], 'number'); ?>
		</div>
		<hr />
	</div>
	<button class="btn btn-success" type="submit">Update</button>
	<a class="btn btn-warning" href="../Courses.php">Cancel</a>
</form>
<?php $HTML->Render() ?>