<?php
	require_once __DIR__ . '\\..\\core\\html.php';
	$viewData = HtmlHelper::$_ViewData;
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<Title><?php echo $viewData['Title'] ?></Title>
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
		integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
		crossorigin="anonymous"></script>
	<link rel="stylesheet" href="/css/bootstrap.min.css" />
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/css/datatables.min.css" />
	<script type="text/javascript" src="/js/datatables.min.js"></script>
</head>

<body class="container">
	<?php echo $viewData['BodyContent'] ?>
</body>

</html>