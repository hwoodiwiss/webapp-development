<?php

require_once __DIR__ . '/core/html.php';

HtmlHelper::$_Title = "401";

?>

<div class="row">
	<h1 class="text-light">Access Denied</h1>
</div>

<?php $GLOBALS["HTML"]->Render() ?>