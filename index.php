<?php

require_once './core/utils.php';
require_once './Model/User.php';

StartSession();
RequireAuth();

include_once "./core/html.php";

HtmlHelper::$_Title = "CourseMan";
?>

<div class="col-md">
	<p>Hello World!</p>
</div>

<?php $HTML->Render() ?>