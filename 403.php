<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1);

require_once __DIR__ . '/core/html.php';

HtmlHelper::$_Title = "403";

?>

<div class="row">
	<h1 class="text-light">Forbidden</h1>
</div>

<?php $GLOBALS["HTML"]->Render() ?>