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

$err = SafeGetValue($_GET, 'err');
if($err !== null)
{
	if($err === 'e01')
	{
		echo('<div class="alert alert-danger" role="alert">Username or Password is Incorrect!<button type="button"
		 class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
	}
}

HtmlHelper::$_Title = "Login";

?>

<div class=" login-container text-center">
<form class="form-signin" action="auth.php" method="POST">
	<img class="mb-4" src="https://getbootstrap.com/docs/4.0/assets/brand/bootstrap-solid.svg" alt="" width="72" height="72">
	<h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
	<label for="inputEmail" class="sr-only">Email address</label>
	<input type="email" id="Email" name="Email" class="form-control" placeholder="Email address" required="" autofocus="">
	<label for="inputPassword" class="sr-only">Password</label>
	<input type="password" id="Password" name="Password" class="form-control" placeholder="Password" required="">
	<button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
	<p class="mt-5 mb-3 text-light">Hugo Woodiwiss© 2019</p>
</form>
</div>

<?php $HTML->Render(true, "_layout_login.php") ?>