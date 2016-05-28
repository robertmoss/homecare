<?php 

	include dirname(__FILE__) . '/partials/pageCheck.php';
	include_once dirname(__FILE__) . '/classes/core/database.php';
	include_once dirname(__FILE__) . '/classes/core/utility.php';
	include_once dirname(__FILE__) . '/classes/core/user.php';
    $thisPage="login";
	
	Utility::debug("login.php: logging in user.",5);
	$username = '';
	$password = '';
	$remember_choice = false;
	$successURL = 'index.php';
    $context = Utility::getRequestVariable('context', '');
    $requestMethod = $_SERVER['REQUEST_METHOD'];
	if (isset($_POST['username'])) {
		$username = trim(htmlspecialchars($_POST['username']));
	}
	if (isset($_POST['password'])) {
    	$password = trim(htmlspecialchars($_POST['password']));
	}
	if (isset($_POST['remember_me'])) {
		$remember_choice = trim($_POST["remember_me"]);
		}
	if (isset($_POST['successURL'])) {
		$successURL = $_POST['successURL'];
	}
    if (isset($_POST['source'])) {
        $source = $_POST['source'];
    }

	$errorMessage = '';
    $expired = false;
    $reset = false;
    $user = new User(0,$tenantID);    	
	// attempt to login user;
    if ($requestMethod=="GET") {
        // not a post, so don't try to load user        
    }
    elseif (strlen($username)<=0 || strlen($password)<=0) {
        $errorMessage = "You must enter both a username and password.";
	}
	else {
		// try to create a new user object
		try {
			$user->validateUser($username, $password, $tenantID);
			Utility::debug('User ' . $user->name . ' logged in succesfully.',5);
			
			// initiate new user session
			$_SESSION['userID'] = $user->id;
			$_SESSION['user_screenname'] = $user->name;
			header( 'Location: ' . $successURL);
			}
		catch (Exception $e) {
		    if ($e->getCode()==1) {
		        // Password has expired. 
		       $expired =true;
               $_SESSION['expiredUserID'] = $user->userid;
		       $errorMessage = "Your password has expired. Please create a new password."; 
		    }
            elseif($e->getCode()==2) {
               // Password has been reset. 
               $reset = true;
               $_SESSION['expiredUserID'] = $user->userid;
               $errorMessage = "Your password has been reset. Please create a new password.";
                }  
            else {	 
    			$errorMessage = $e->getMessage();
            }
			Utility::debug('Login failed: ' . $errorMessage,9);
		}
	}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Food Finder: Login</title>
        <?php include("partials/includes.php"); ?>
        <script type="text/javascript" src="js/login.js"></script>  
    </head>
    <body>
    	<?php 
                    include('partials/header.php');

                ?>
        <div >
        <div class="container">
    	<?php if(strlen($errorMessage)>0) { ?>
    		<div id="openError" class="edit">
    			<div class="alert alert-danger">
    				<?php echo $errorMessage; ?>
    			</div>
    		</div>
    	<?php } 
            if($context=='loginRequired') { ?>
            <div class="edit">
                <div class="alert alert-info">
                    <p>You must be logged in to access the requested resource.</p>
                </div>
            </div>

        <?php }
            if ($user->id==0 && !$expired && !$reset)  { ?>
    		<div id="loginForm" class="panel panel-default">
    		    <div class="panel-body">
        		    <form action="login.php" method="post">
    				    <input id="txtSource" name="source" type="hidden" value="login.php" />
    					<div class="form-group">
    						<label for="txtUserName">Username</label>
    						<input id="txtUsername" name="username" type="text" class="form-control" placeholder="Username"></input>								
    					</div>
    					<div class="form-group">
    						<label for="txtPassword">Password</label>
    						<input id="txtPassword" name="password" type="password" class="form-control" placeholder="Password"></input>								
    					</div>
    					<div class="form-group">
    						<input type="button" class="btn btn-default" value="Cancel" onclick="hideElement('topnav_login');"/>	
                            <input type="submit" class="btn btn-primary" value="Submit"/>
    					</div>
    				</form>
    				</div>
    	       </div>
    	    <?php } elseif ($user->id==0 && ($expired || $reset))  { ?>
				<div id="passwordPanel" class="panel panel-default">
                      <div class="panel-heading">
                        <h3 class="panel-title">Change Password</h3>
                      </div>
                      <div class="panel-body">
                            <form id="frmChangePassword" class="form-horizontal" data-toggle="validator" action="service/user.php?action=changePass" method="POST" onsubmit="changePassword(); return false;">
                                <input type="hidden" id="txtPasswordUserId" name="id" value="<?php echo $userID ?>">
                                <?php if(!$reset) { ?>
                                <div class="form-group">
                                    <label for="txtOldPassword1" class="col-sm-4 control-label">Old Password:</label>
                                    <div class="col-sm-3">
                                        <input type="password" name="original" data-minlength="8" data-minlength-error="Passwords must be at least 8 characters long" class="form-control" id="txtNewPassword1" placeholder="New Password" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                                <?php } ?>
                                <div class="form-group">
                                    <label for="txtNewPassword1" class="col-sm-4 control-label">New Password:</label>
                                    <div class="col-sm-3">
                                        <input type="password" name="new1" data-minlength="8" data-minlength-error="Passwords must be at least 8 characters long" class="form-control" id="txtNewPassword1" placeholder="New Password" required>
                                    </div>
                                    <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                    <label for="txtNewPassword2" class="col-sm-4 control-label">Retype New Password:</label>
                                    <div class="col-sm-3">
                                        <input type="password" name="new2" data-minlength="8" data-minlength-error="Passwords must be at least 8 characters long" class="form-control" id="txtNewPassword2" placeholder="Retype New Password" data-match="#txtNewPassword1" data-match-error="The two new passwords must match." required>
                                    </div>
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    <span class="glyphicon form-control-feedback" aria-hidden="true"></span>
                                    <span id="passwordStatus" class="sr-only">(warning)</span>
                                    <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-4 col-sm-10">
                                          <button id="btnPasswordSubmit" type="submit" class="btn btn-primary">Update Password</button>
                                    </div>
                                </div>
                            </form>
                             
                      </div>
                    </div>
			    <div id="password-message" class="alert alert-danger hidden">
                    <a class="close_link" href="#" onclick="hideElement('password-message');"></a>
                    <span id='password-message-text'>Message goes here.</span>
                </div>
                 <div id="loginButton" class="col-sm-offset-2 col-sm-10 hidden">
                        <a href="login.php" id="btnPasswordSubmit" type="button" class="btn btn-primary">Login</a>
                         
                 </div>
            </div>
    	<?php } ?>	
    	</div>
    	<?php 
                 include('partials/footer.php');?>
    </body>
</html>
    