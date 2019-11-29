<?php

require_once __DIR__ . './core/html.php';

HtmlHelper::$_Title = "404";

?>

<div class="row">
	<h1 class="text-light">Page Not Found</h1>
</div>

<?php $GLOBALS["HTML"]->Render() ?>