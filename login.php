<?php
require_once './core/utils.php';
require_once './core/html.php';
HtmlHelper::$_Title = 'Login';

if(!$_SERVER['REQUEST_METHOD'] === 'GET')
{
	header('HTTP/1.0 405 Method Not Allowed', 405);
	die();
}

$err = SafeGetValue($_GET, 'err');
if($err !== null)
{
	if($err === 'UsernameOrPassword')
	{
		echo('<div class="alert alert-danger" role="alert">Username or Password is Incorrect!<button type="button"
		 class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');
	}
}

?>

<form action="auth.php" method="POST">
	<label>
		Email Address
	</label>
	<input class="form-control" type="email" name="Email" />
	<label>
		Password
	</label>
	<input class="form-control" type="password" name="Password" />
	<button class="btn btn-success" type="submit">Submit</button>
</form>

<?php $HTML->Render() ?>