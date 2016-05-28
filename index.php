<?php
	include dirname(__FILE__) . '/partials/pageCheck.php';
	include_once dirname(__FILE__) . '/classes/core/utility.php';
	$thisPage="index";
 ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo Utility::getTenantProperty($applicationID, $_SESSION['tenantID'],$userID,'title') ?></title>
		<?php include("partials/includes.php"); ?>
        <script src="js/jquery-ui.js"></script>
    </head>
    <body>
        <?php include("partials/header.php"); ?>
    	<div class="container">
			<h1>Hello</h1>
        </div>
        <?php include("partials/footer.php");?>             
    </body>
</html>