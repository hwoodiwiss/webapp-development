<?php
	require_once __DIR__ . '\\..\\core\\html.php';
	$viewData = HtmlHelper::$_ViewData;
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<link rel="stylesheet" href="/css/bootstrap.min.css" />
	<Title><?php echo $viewData['Title'] ?></Title>
	<script type="text/javascript" src="/js/jquery-3.4.1.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script type="text/javascript" src="/js/bootstrap.min.js"></script>
	<!-- link rel="stylesheet" type="text/css" href="/css/datatables.min.css" /-->
	<!-- script type="text/javascript" src="/js/datatables.min.js"></script -->
	<link rel="stylesheet" type="text/css" href="/css/courseman.css" />
	<script type="text/javascript" src="/js/jquery.validate.min.js"></script>
	<script type="text/javascript" src="/js/courseman.js"></script>
</head>
	<body class="bg-secondary text-light">
		<?php include 'navbar.php' ?>
		<div class="container">
			<?php echo $viewData['Body'] ?>
			<div id="MasterAlerts"></div>
		</div>
	</body>
</html>