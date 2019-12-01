<?php

require_once __DIR__ . '/core/html.php';

HtmlHelper::$_Title = "400";

?>

<div class="row">
	<h1 class="text-light">Bad Request</h1>
</div>

<?php $GLOBALS["HTML"]->Render() ?>