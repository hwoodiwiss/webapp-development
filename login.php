<?php
require_once './core/utils.php';
require_once './core/html.php';
HtmlHelper::$_Title = 'Login';

if(!$_SERVER['REQUEST_METHOD'] === 'GET')
{
	header('HTTP/1.0 405 Method Not Allowed', 405);
	die();
}

$location = SafeGetValue($_GET, "location");

StartSession();
if(SafeGetValue($_SESSION, "auth") == true)
{
	if($location != null)
	{
		header("Location: " . urldecode($location));
	}
	else
	{
		header("Location: /index.php");
	}
	exit();
}

$err = SafeGetValue($_GET, 'err');
if($err !== null)
{
	
}

HtmlHelper::$_Title = "Login";

?>

<?php if($err === 'e01'): ?>
	<script>
		CM.Alert("Username Or Password!", "The provided username or password was incorrect!", )
	</script>
<?php endif; ?>

<div class="login-container">
	<div class="row">
		<div class="col"></div>
		<div class="col">
			<div class="card login-card text-dark">
				<div class="card-body form-row">
					<form class="form-signin async no-validate" action="auth.php" method="POST"
						onsuccess="window.location = data.Location;"
						onfail="CM.Alert(data.Heading, data.Content, data.Type);">
						<input type="hidden" name="location" value="<?php echo $location ?>">
						<img class="mb-4" src="/content/Mortarboard.svg" alt="CourseMan" width="144" height="72">
						<h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
						<div class="form-group col-12">
							<label for="inputEmail" class="sr-only">Email address</label>
							<input type="email" id="Email" name="Email" class="form-control" placeholder="Email address"
								required autofocus>
						</div>
						<div class="form-group col-12">
							<label for="inputPassword" class="sr-only">Password</label>
							<input type="password" id="Password" name="Password" class="form-control"
								placeholder="Password" required>
						</div>
						<div class="form-group col-12">
							<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
						</div>
						<p class="mt-5 mb-3 text-dark">Hugo Woodiwiss© 2019</p>
					</form>
				</div>
			</div>
		</div>
		<div class="col"></div>

	</div>
</div>
<?php $HTML->Render(true, "_layout_login.php") ?>