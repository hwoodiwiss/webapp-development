<?php

$loggedIn = false;
$User = null;
$isAdmin = false;
$auth = SafeGetValue($_SESSION, 'auth');
if ($auth != null && $auth == true) 
{
  $loggedIn = true;
  $User = SafeGetValue($_SESSION, 'User');
  $isAdmin = $User->AccessLevelId;
}
?>

<nav class="navbar navbar-light bg-light">
  <a class="navbar-brand" href="/index.php">CourseMan</a>
</nav>