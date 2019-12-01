<?php
require_once '../../Model/User.php';
require_once '../../Model/Course.php';
require_once '../../core/utils.php';
require_once '../../core/html.php';

StartSession();
RequireAuth();

$user = SafeGetValue($_SESSION, 'User');

if($user->AccessLevel->Name != 'Admin')
{
	ErrorResponse(401);
}

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
	$Title = ValidatePOSTValue('Title');
	$StartDate = ValidatePOSTValue('StartDate');
	$Description = ValidatePOSTValue('Description');
	$Duration = ValidatePOSTValue('Duration');
	$Capacity = ValidatePOSTValue('Capacity');

	$NewCourse = new Course();
	$NewCourse->Name = $Title;
	$NewCourse->StartDate = $StartDate;
	$NewCourse->Description = $Description;
	$NewCourse->Duration = $Duration;
	$NewCourse->Capacity = $Capacity;
	$NewCourse->Active = true;

	$Courses->InsertObj($NewCourse);

	header('Location: ../Courses.php');
}

HtmlHelper::$_Title = 'Admin: Add Course';

?>
<h3>New Course</h3>
<hr />
<form action="./Add.php" method="POST">
<div class="form-row">
		<div class="form-group col-6">
			<label>Title</label>
			<?php $HTML->Input("", ['id' => 'Title', 'placeholder' => 'Course Title', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-6">
			<label>Start Date</label>
			<?php $HTML->Input("", ['id' => 'StartDate', 'placeholder' => 'Course Start Date', 'class' => 'form-control', "required" => ""], 'date'); ?>
		</div>
		<div class="form-group col-12">
			<label>Description</label>
			<?php $HTML->Input("", ['id' => 'Description', 'placeholder' => 'Course Description', 'class' => 'form-control', 'maxlength' => 255, "required" => ""]); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Duration</label>
			<?php $HTML->Input("", ['id' => 'Duration', 'placeholder' => 'Course Duration (Days)', 'class' => 'form-control', "required" => ""], 'number'); ?>
		</div>
		<div class="form-group col-md-6">
			<label>Capacity</label>
			<?php $HTML->Input("", ['id' => 'Capacity', 'placeholder' => 'Course Capacity', 'class' => 'form-control', "required" => ""], 'number'); ?>
		</div>
		<hr />
	</div>
	<button class="btn btn-success" type="submit">Save</button>
	<a class="btn btn-warning" href="../Courses.php">Cancel</a>
</form>

<?php $HTML->Render() ?>