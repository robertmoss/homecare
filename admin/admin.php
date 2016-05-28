<?php 
	
	include dirname(__FILE__) . '/../partials/pageCheck.php';
    include dirname(__FILE__) . '/../classes/core/resource.php';
	$thisPage = 'admin';
    $message = '';
    $messageType='success';
	
    // must be an admin for current tenant to access this page
    if ($userID==0 || ($user && !$user->hasRole('admin',$tenantID))) {
        Log::debug('Non admin user (id=' . $userID . ') attempted to access admin.php page', 10);
        header('Location: 403.php');
        die();
    }
    
	$newtenant = Utility::getRequestVariable('newtenant', 0);

	// verify user can access requested tenant, then switch & force reload
	if ($newtenant>0 && $newtenant!=$tenantID) {
		if ($user->canAccessTenant($newtenant)) {
			$_SESSION['tenantID'] = $newtenant;
			$tenantID=$newtenant;
			header("Refresh:0");
		}
		else {
			echo 'Sorry - can\'t switch that tenant. No sure how that happened . . .'; 
		}
    }    
   
    $flush = Utility::getRequestVariable('flushCache', 'no');
    if ($flush=="yes" || $flush=="true") {
        Cache::flushCache();
        $message = "Cache flushed.";
        }
    
	
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title><?php echo Resource::getString("ApplicationName") . " - " . Resource::getString("Admin") ?></title>
        <?php include("../partials/includes.php"); ?>
        <script type="text/javascript" src="../js/validator.js"></script>
        <script type="text/javascript" src="../js/bootpag.min.js"></script>
        <script type="text/javascript" src="../js/jquery.form.min.js"></script>
		<script type="text/javascript" src="../js/admin.js"></script>
    </head>
    <body>
    	<div id="maincontent">
    		<div id="outer">
	    		<?php include('../partials/header.php');?>
	        	<div class="container">
		        	<div>
		        		<ul class="nav nav-pills" role="tablist">
		        			<li role="presentation" class="active"><a href="#general" aria-controls="general" role="tab" data-toggle="tab">General</a></li>
							<li role="presentation"><a href="#useradmin" aria-controls="useradmin" role="tab" data-toggle="tab">Users</a></li>
							<li role="presentation" ><a href="#tenantadmin" aria-controls="tenantadmin" role="tab" data-toggle="tab" onclick="loadTenants();">Tenants</a></li>
							<li role="presentation" ><a href="#import" aria-controls="import" role="tab" data-toggle="tab">Import</a></li>
						</ul>	        		
		        	</div>
		        	<div class="tab-content">
		        		<div id="general" role="tabpanel" class="tab-pane active">
		        			<h1>General</h1>
		        			<div id="message" class="alert alert-<?php echo $messageType;?> alert-dismissible <?php if (!$message) { echo 'hidden';} ?> ">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <span id='message_text'><?php echo $message ?></span>
                            </div>
		        			<div class="tenantSwitcher container">
	    			 			<form id="tenantSwitcherForm" action="#" method="post" role="form" >
	    			 				<div class="form-group">
										<label for="">Current Tenant:</label>
										<select class="form-control" id="current-tenant" name="tenantid" required>
											<?php Utility::renderOptions('tenants', $tenantID, $userID, $tenantID); ?>
										</select>
				    			 	</div>
				    			 </form>
	    					</div>
	    					<div class="panel">
	    					    <form id="tenantSwitcherForm" action="admin.php?flushCache=yes" method="post" role="form" >
    	    					    <input class="btn btn-default" type="submit" value="Clear Cache" />
    	    					    <h3>Tenant Settings</h3>
                                    <div class="row">
                                        <div class="col-md-3">Current Tenant Style Sheet (css)</div>
                                        <div class="col-md-3"><?php echo Utility::getTenantProperty($applicationID, $tenantID, $userID, 'css'); ?></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3">Show Ads (showAds)</div>
                                        <div class="col-md-3"><?php echo Utility::getTenantProperty($applicationID, $tenantID, $userID, 'showAds'); ?></div>
                                    </div>
    	    					</form>
	    					</div>
	    				</div>
			        	<div id="useradmin"  role="tabpanel" class="tab-pane">
				        	<h1>Manage Users</h1>
				        	<div id="user-buttons" class="btn-group btn-default">
    							<button class="btn btn-default" id="adduser" onclick="addUser();">
        							<span class="glyphicon glyphicon-plus"></span> Add User
    							</button>
							</div>
							<div id="alertZone"></div>
				        	<div id="userList">
				        		<span id="resultSpan">Loading users . . .</span>
				        	</div>
				        	<div id="page-selection"></div>
			        	</div>
			        	<div id="tenantadmin"  role="tabpanel" class="tab-pane">	
			        		<h1>Manage Tenants</h1>
			        		<?php if ($user->hasRole('superuser',$tenantID)) { ?>
			        		<div id="tenant-buttons" class="btn-group btn-default">
    							<button class="btn btn-default" id="addtenant" onclick="addTenant();">
        							<span class="glyphicon glyphicon-plus"></span> Add Tenant
    							</button>
                            </div>
    						<?php } ?>
							<div id="alertZoneTenant"></div>
							<div id="tenantList">
				        		<span id="tenantResultSpan">Loading tenants . . .</span>
				        	</div>
							<div id="tenantPageSelection"></div>
			        	</div>
			        	<div id="import" role="tabpanel" class="tab-pane">
				        	<h1>Import Files</h1>
				        	<div id="import-message" class="alert alert-danger hidden">
		        				<span id='import-message-text'>Message goes here.</span>
		        			</div>
				        	<form id="uploadForm" action="../service/upload.php" method="post" enctype="multipart/form-data" role="form">
				        		<div class="form-group">
				        			<label for="importFile">Choose file to import:</label>
				        			<input id="importFile" type="file" name = "importFile" id="importFile"/>
				        		</div>
				        		 <button id="importSubmit" type="submit" class="btn btn-primary">Import</button>
				        		 <button id="importCancel" type="button" class="btn btn-default" onclick="cancelImport();">Cancel</button>
				        	</form>
				        	<div id="progress-wrapper" class="progress-wrapper" style="display: none;">
				        		<div id="output"></div>
					        	<div id="importProgress" class="progress">
	  								<div id="importProgressBar" class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
	    								<span class="sr-only">Initiating</span>
	  								</div>
								</div>
								<div id="importProgressText"></div>
							</div>
			        	</div>
			        </div>
			        <!-- Modal -->
					<div id="userModal" class="modal fade" role="dialog">
					  <div class="modal-dialog">
					    <!-- Modal content-->
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal">&times;</button>
					        <h4 id="userHeader" class="modal-title">Edit User</h4>
					      </div>
					      <form id="userForm" action="../service/user.php" method="post" role="form" >
						      <div id="userBody" class="modal-body locationModal">
					        	<input type="hidden" class="form-control" id="input-id" name="id"></input>
					        	<div class="form-group">
					        		<label for="input-name">Username</label>
					        		<input type="text" class="form-control" id="input-name" name="name" required></input>
					        		<div class="help-block with-errors"></div>
					        	</div>
					        	<div class="form-group">
					        		<label for="input-email">Email</label>
					        		<input type="email" class="form-control" id="input-email" name="email" required></input>
					        		<div class="help-block with-errors"></div>
					        	</div>
					        	<div id="user-passwordControls" class="hidden">
					        		<div id="user-passwordGroup" class="form-group">
					        			<label for="input-password">Password</label>
					        			<input type="password" data-minlength="8" class="form-control" id="input-password" name="password" aria-describedby="passwordStatus" required></input>
										<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
  										<span id="passwordStatus" class="sr-only">(warning)</span>
  										<div class="help-block">Minimum 8 characters</div>
  									</div>
  									<div id="user-password2Group" class="form-group">
						        		<label for="input-password2">Retype Password</label>
						        		<input type="password" class="form-control" id="input-password2" aria-describedby="passwordsStatus" data-match="#input-password" data-match-error="Both passwords must match." required></input>
						        		<span class="glyphicon form-control-feedback" aria-hidden="true"></span>
  										<span id="password2Status" class="sr-only">(warning)</span>
  										<div class="help-block with-errors"></div>
  									</div>
					        	</div>
					      </div>
					      <div class="modal-footer">
							<div id="user-message" class="alert alert-danger hidden">
		        				<a class="close_link" href="#" onclick="hideElement('message');"></a>
		        				<span id='user-message_text'>Message goes here.</span>
		        			</div>
					        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
					        <button type="button" class="btn btn-default" onclick="resetForm();">Reset</button>
					        <button id="userSave" type="submit" class="btn btn-default">Save</button>
					      </div>
					      </form>
					    </div>
					  </div>
					</div>
					<div id="tenantEditModal" class="modal fade" role="dialog">
					  <div class="modal-dialog">
					    <!-- Modal content-->
					    <div class="modal-content">
    					      <div class="modal-header">
    					        <button type="button" class="close" data-dismiss="modal">&times;</button>
    					        <h4 id="tenantHeader" class="modal-title">Edit Tenant</h4>
    					      </div>
    					      <div id="tenantFormAnchor" class="modal-body">Loading form . . .</div>
    					      <div class="modal-footer">
                                    <div id="tenant-message" class="alert alert-danger hidden">
                                        <a class="close_link" href="#" onclick="hideElement('message');"></a>
                                        <span id='tenant-message_text'>Message goes here.</span>
                                    </div>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-default" onclick="resetForm();">Reset</button>
                                    <button id="userSave" type="button" class="btn btn-default" onclick="saveTenant();">Save</button>
                             </div>
					    </div>
					  </div>
					</div>
		        </div>
		        <div id="manageTenantsModal" class="modal fade" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 id="manageTenantsHeader" class="modal-title">Manage User Tenants</h4>
                              </div>
                              <div id="manageTenantsAnchor" class="modal-body">Loading form . . .</div>
                              <div class="modal-footer">
                                    <div id="manageTenants-message" class="alert alert-danger hidden">
                                        <a class="close_link" href="#" onclick="hideElement('message');"></a>
                                        <span id='manageTenants-message_text'>Message goes here.</span>
                                    </div>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="saveUserTenants();">Save</button>
                                    <select id="role-select" class="hidden">
                                        <?php 
                                        $roles = Utility::getList('roles',$tenantID,$userID);
                                        for($i=0;$i<count($roles);$i++) {
                                            echo '<option value="' . $roles[$i] . '">' . $roles[$i] .'</option>';
                                       }
                                        ?>
                                    </select>
                             </div>
                        </div>
                      </div>
                    </div>
                <?php include("../partials/childEditModal.php")?>	
        		<?php include("../partials/footer.php")?>     		
        	</div>
        </div>
    </body>
</html>
    