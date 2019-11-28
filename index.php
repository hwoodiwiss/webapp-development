<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once './core/utils.php';
require_once './Model/User.php';

session_start();

if(SafeGetValue($_SESSION, 'auth') !== null && $_SESSION['auth'] == true)
{
	$user = SafeGetValue($_SESSION, 'User');
}
else
{
	header('Location: /login.php');
}

include_once "./core/html.php"
?>

<div class="col-md">

</div>

<?php $HTML->Render() ?>