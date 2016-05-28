<?php 
     include_once dirname(__FILE__) . '/../classes/config.php';
     ?>
        <link rel="shortcut icon" type="image/x-icon" href="img/icons/ff_favicon.ico" />
        <link rel="stylesheet" type="text/css" href="<?php echo Config::$site_root ?>/css/styles.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Config::$site_root ?>/css/bootstrap.css" />	
        <link rel="stylesheet" type="text/css" href="<?php echo Utility::getTenantProperty($applicationID, $tenantID, $userID, 'css'); ?>" />	
    
		<script src="<?php echo Config::$site_root ?>/js/jquery-1.10.2.js"></script>
		<script src="<?php echo Config::$site_root ?>/js/mustache.js"></script>
		<script src="<?php echo Config::$site_root ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo Config::$site_root ?>/js/core.js"></script>
		
		
