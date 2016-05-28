<?php 
    include dirname(__FILE__) . '/partials/pageCheck.php';
        $thisPage="index";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo Utility::getTenantProperty($applicationID, $_SESSION['tenantID'],$userID,'title') ?></title>
        <?php include("partials/includes.php"); ?>
    
		<script src="js/jquery-1.10.2.js"></script>		
		<script src="js/mustache.js"></script>
		<script src="js/core.js"></script>
    </head>
    <body>
    	<div id="maincontent">
    		<div id="outer">
	    		<?php include('partials/header.php');?>
    			<div id="main">
    				<h1>Uh oh.</h1>
    				<p>Something appears to have gone wrong.</p>
    				<?php
    				// if error message set on session, display to user.
	    				if (isset($_SESSION['errorMessage'])) {
	    					echo "<p>" . $_SESSION['errorMessage'] . "</p>";
					}
					?>
	        	</div>	
        		<?php include("partials/footer.php")?>     		
        	</div>
        </div>
    </body>
</html>
    