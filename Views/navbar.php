<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once __DIR__ . "\\../core/utils.php";
require_once __DIR__ . "\\../Model/User.php";

StartSession();
$loggedIn = false;
$User = null;
$isAdmin = false;
$auth = SafeGetValue($_SESSION, 'auth');
if ($auth != null && $auth == true) 
{
	$loggedIn = true;
	$User = SafeGetValue($_SESSION, 'User');
	$isAdmin = $User->AccessLevel->Name == "Admin";
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
	<a class="navbar-brand" href="/index.php">CourseMan</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
	<ul class="navbar-nav mr-auto">
		<li class="nav-item">
			<a class="nav-link" href="/index.php">Home</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" href="/Courses.php">Courses</a>
		</li>
		<?php if($isAdmin): ?>
		<li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				Admin
			</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
				<a class="dropdown-item" href="/Admin/Users.php">Users</a>
				<a class="dropdown-item" href="/Admin/Courses.php">Courses</a>
			</div>
		</li>
		<?php endif; ?>
	</ul>
	<?php if($loggedIn): ?>
		<a class="text text-light" href="/logout.php">Logout</a>
	<?php endif; ?>
	</div>
</nav>