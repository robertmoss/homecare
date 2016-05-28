<?php 
    include dirname(__FILE__) . '/partials/pageCheck.php';
    $thisPage="REPLACE ME"; 
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Access Denied</title>
        <?php include("partials/includes.php"); ?>
    </head>
    <body>
        <div id="maincontent">
            <div id="outer">
                <?php include('partials/header.php');?>
                <div id="main" class="container">
                    <h1>Sorry . . .</h1>
                    <p>You are not authorized to access the requested resource.</p>
                    <p><a href="index.php">Return to Home Page</a></p>
                </div>  
                <?php include("partials/footer.php")?>           
            </div>
        </div>
    </body>
</html>
    