<?php

require_once './core/utils.php';
require_once './Model/User.php';

StartSession();
RequireAuth();

include_once "./core/html.php";

HtmlHelper::$_Title = "CourseMan";
?>

<div class="col-6">
	<p>Hello World!</p>
</div>
<div class="col-6">

</div>

<?php $HTML->Render() ?>