<?php
    
    if (!isset($_SESSION['APIKey']) || !isset($_GET['APIKey'])) {
		//header(' ', true, 403);
		//echo 'Missing or invalid APIKey. Be sure to login and obtain API Key before accessing API services.';
		//die();
		Utility::debug('Service called without APIKey. We really need to get around to implementing that.', 1);
	}
	else {
		//echo $_SESSION['APIKey'];
	}
	
?>