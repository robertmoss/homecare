/**
 * @author Robert Moss
 * This is a library file that can be used with the core objects on admin pages.
 */
var count=0;
var timer;
const USERS_PER_PAGE = 10;
var offset=0;
var batchid=0;
var totalitems=1;

$(document).ready(function() {
	var options = { 
	    target:   '#output',   // target element(s) to be updated with server response 
	    beforeSubmit:  beforeSubmit,  // pre-submit callback 
	    success:       afterSuccess,  // post-submit callback 
	    uploadProgress: onProgress, //upload progress callback 
	    error: 			onError,	// function to call if an error
	    resetForm: true        // reset the form after successful submit 
	}; 
	
        
 	$('#uploadForm').submit(function() { 
    	$(this).ajaxSubmit(options);            
    	return false; // return false to prevent standard browser submit and page navigation 
	});
	
	$('#userForm').validator({disable: true});
	
	$('#userForm').validator().on('submit', function (e) {
  		if (!e.isDefaultPrevented()) {
  			e.preventDefault(); // don't actually submit form; we submit via AJAX
  			saveUser();
  			}
 
		});

	$('#tenantForm').validator({disable: true});
	
	$('#tenantForm').validator().on('submit', function (e) {
  		if (!e.isDefaultPrevented()) {
  			e.preventDefault(); // don't actually submit form; we submit via AJAX
  			saveTenant();
  			}
 
		});
	
	$('#current-tenant').change(function() {
		var newid = $('#current-tenant').val();
		location.href = "admin.php?newtenant=" + newid;
	});
	
	retrieveUsers(0);
	// load User List
	
});

function progressCheck() {
	count++;
	serviceURL = 'service/batchStatus.php?id=' + batchid;

	if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		    var view = JSON.parse(xmlhttp.responseText);
		    items = view.items;
		    processed = view.processed;
		    percentComplete = Math.round((processed/items)*100);
		    $('#importProgress').show();
		    var width = percentComplete + '%';
			document.getElementById('importProgressBar').style.width = width;
			document.getElementById('importProgressBar').innerHTML = '';
			document.getElementById('importProgressText').innerHTML = 'Processed: ' + percentComplete +'% (' + processed + ' of ' + items + ' items)';
	
			// if batch still running, check again in 3 more seconds
			if (view.status=='running')
				{
					setTimeout(progressCheck,3000);
				}
			else {
				if (view.status='canceled') {
					document.getElementById('importProgressText').innerHTML = 'Import canceled.';
					}
				}
		   }
		 };
		xmlhttp.open("GET",serviceURL,true);
		xmlhttp.send();
		

	}

function beforeSubmit() {

	if( !$('#importFile').val()) // no file selected.
		{
			setMessage('Please select a file to upload.','import-message','import-message-text',false);
			return false;
		}

   	//check that client browser fully supports required File APIs
   if (window.File && window.FileReader && window.FileList && window.Blob) {
		// we are good - check file size
		var maxsize = 5242880; // 5 megabytes max upload
    	var fsize = $('#importFile')[0].files[0].size;
    	if (fsize>maxsize) {
    		var message = 'File is too large to load. Maximum allowed size is 5 MB.';
    		setMessage(message,'import-message','import-message-text',false);
    		return false;
    		}
    	var ftype = $('#importFile')[0].files[0].type;
    	switch(ftype) {
    		case 'application/vnd.google-earth.kml+xml':
    			// allowed type
    		break;
    		default:
    			var message = 'Unsupported file type.'; 
    			setMessage(message,'import-message','import-message-text',false);
    			return false;
    	}
		count=0;

		$('#importSubmit').hide(); //hide submit button
		$('#importCancel').show(); //show  cancel button
		document.getElementById('importProgressBar').className ="progress-bar progress-bar-info";
		document.getElementById('importProgressBar').innerHTML = 'Preparing to upload file . . .'; 
		$('#progress-wrapper').show();
		return true;
        }
    else {
       // we have older unsupported browser that doesn't support HTML5 File API
       alert("Your current browser does not support the file upload feature. HTML5-compliant browser required.");
    }
}

function onProgress(event, position, total, percentComplete) {
	$('#importProgress').show();
	document.getElementById('importProgressBar').style.width = percentComplete + '%;';
	document.getElementById('importProgressBar').innerHTML = 'Uploading: ' + percentComplete +'%';
}

function onError(event,status,error) {
	setMessage('File upload failed:' + event.responseText,'messagebox','message',false);
	cleanUp();
}

function afterSuccess(responseText,statusText,xhr,$other) {
	if (statusText=='success') {
		batchid = responseText.batchid;
		totalitems = responseText.count;
		setTimeout(progressCheck,2000);
	}
	
}

function cancelImport() {
	 document.getElementById('importProgressBar').innerHTML = 'Canceling import... ';
	 document.getElementById('importProgressBar').className ="progress-bar progress-bar-warning"; 

	// call service to cancel batch
	count++;
	serviceURL = 'service/batchStatus.php?id=' + batchid + '&action=cancel';

	if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		    var view = JSON.parse(xmlhttp.responseText);
		    }
		 };
		xmlhttp.open("GET",serviceURL,true);
		xmlhttp.send();

	cleanUp();
}

function cleanUp() {
	clearInterval(timer);
	$('#importSubmit').show(); //show submit button
	$('#importCancel').hide(); //hide loading button
	showElement('output');

	$('#progress-wrapper').delay( 1000 ).fadeOut(); //hide progress bar
}

function retrieveUsers(offset) {
	
	var template = "<table class=\"table table-striped table-hover table-responsive\">";
	template += "<thead><tr><th>Name</th><th>Last Sign In</th><th>Actions</th></tr></thead>";
	template += "<tbody>{{#users}}"; 	
	template += "<tr><td><div class=\"user\"><span class=\"description\">{{name}}</span></div></td>";
	template += "<td><div class=\"user\"><span class=\"description\">{{lastSignIn}}</span></div></td>";
	template += "<td><div class=\"btn-group btn-group-sm\" role=\"group\" aria-label=\"...\">";
	//template += "<a href=\"user.php?type=user&id={{id}}\">edit</a>";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"editUser({{id}});\"><span class=\"glyphicon glyphicon-pencil\"></span>&nbsp;</button>";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"deleteUser({{id}});\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;</button>";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"resetPassword({{id}});\">Reset Password</button>";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"manageTenants({{id}});\">Tenant Access</button>";
	//template += "<a href=\"#\" onclick=\"resetPassword({{id}});\">reset password</a>";
	template += "</div></td></tr>";
	template += "{{/users}}</tbody></table>";

	
	var serviceURL = "../service/users.php";
	serviceURL += '?offset=' + offset;
	var working = "Retrieving users . . .";
	var anchor = "resultSpan";	
					
	if (working.length>0)
		{
		document.getElementById(anchor).innerHTML = working;
		}
		
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	  
	 xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4) {
		  	hideElement('loading');
		  	if (xmlhttp.status==200) {
			    var view = JSON.parse(xmlhttp.responseText);
				renderTemplate(template,view,anchor);
				var totalUsers=view.totalUsers;
				var numPage = Math.ceil(totalUsers/USERS_PER_PAGE);
				$('#page-selection').bootpag({total: numPage}).on("page",function(event,num) {
					offset = (num-1) * USERS_PER_PAGE;
					retrieveUsers(offset);
					});
				}
			else {
				document.getElementById(anchor).innerHTML = 'unable to load users:' + xmlhttp.responseText;
				}
		    }
		 };
	xmlhttp.open("GET",serviceURL,true);
	xmlhttp.send();
}

function resetForm() {
	document.getElementById("userForm").reset();
}

function addUser() {
	document.getElementById("userForm").reset();
	document.getElementById("input-id").value=null;
	hideElement("user-message");
	showElement("user-passwordControls");
	$('#userModal').modal();
}

function editUser(id) {
	document.getElementById("userForm").reset();
	hideElement("user-message");
	hideElement("user-passwordControls");
	serviceURL = "../service/user.php?id=" + id;
	getAndRenderForm(serviceURL,'userForm','input-',userFormLoaded);
	$('#userModal').modal();
}

function userFormLoaded(success,message) {
	if (!success) {
		setElementText('user-message',message);
		showElement('user-message');
		disableElement('userSave');
	}
}

function saveUser() {
	try {
		submitForm('userForm','user-message','user-message_text',false,'input-id',onSave);
		}
		catch(ex) {
			// do nothing for now - message set in submitForm
		}	
}

function onSave(success) {

	if (success) {
		$('#userModal').modal('hide');
		retrieveUsers(offset);
	}
}

function deleteUser(id) {

	if (confirm("Are you sure you want to delete this user?"))
		{
		var serviceURL = "../service/user.php";
		serviceURL += "?id=" + id; 
		
		xmlhttp=new XMLHttpRequest();
	 	xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4) {
		  	if (xmlhttp.status==200) {
		  		addAlert('User deleted','alertZone','success',3000);
		  		retrieveUsers(offset);
				}
			else {
				var msg = 'Unable to delete user:' + xmlhttp.responseText;
				addAlert(msg,'alertZone','error');
				}
		    }
		 };
	xmlhttp.open("DELETE",serviceURL,true);
	xmlhttp.send();	
		
		}
}

function manageTenants(userid) {
	
	hideElement("manageTenants-message");
	$('#manageTenantsModal').modal();

	var roleList = document.getElementById('role-select');
	var roles = '';
	for (i=0;i<roleList.childElementCount;i++) {
		roles += '<option value="' + roleList.options[i].value + '">' + roleList.options[i].innerText + '</option>';
	} 
	
	var template = "<input id=\"currentUserId\" type=\"hidden\" value=\"" + userid +"\"/>"; 		
	template += "<table id=\"userTenantsTable\" class=\"table table-striped table-hover table-responsive\">";
	template += "<thead><tr><th>Tenant</th><th>Role</th><th>Remove</th></tr></thead>";
	template += "<tbody>{{#tenants}}"; 	
	template += "<tr><td class=\"hidden\">{{id}}</td><td>{{tenant}}</td>";
	template += "<td>{{role}}</td>";
	template += "<td><div class=\"btn-group btn-group-sm\" role=\"group\" aria-label=\"...\">";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"removeTenant({{index}});\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;</button>";
	template += "</div></td></tr>";
	template += "{{/tenants}}";
	template += "<tr><td><select id=\"tenantSelect\" name=\"tenant\" class=\"form-control\"></select></td>";
	template += "<td><select id=\"roleSelect\" name=\"roles\" class=\"form-control\">" + roles + "</select></td>";
	template += "<td><button type=\"button\" class=\"btn btn-default\" onclick=\"addUserTenant();\">Add</button></td></tr>";
	template += "</tbody></table>";
	
	var serviceURL = "../service/user.php";
	serviceURL += '?id=' + userid;
	var working = "Retrieving tenant information . . .";
	var anchor = "manageTenantsAnchor";

	getAndRenderJSON(serviceURL,template,anchor,working, function(success) {
		loadTenantSelect();
	}); 
	
}

function addUserTenant() {
	
	var sel = document.getElementById('tenantSelect');
	if (sel.selectedIndex>=0) {
		var table = document.getElementById('userTenantsTable');
		var rowIndex = table.rows.length-1; // add new row as second from last.
		var row = table.insertRow(rowIndex); 
		var cell=row.insertCell();
		
		cell.innerHTML = sel.options[sel.selectedIndex].value;
		cell.className = "hidden";
		cell=row.insertCell();
		cell.innerHTML = sel.options[sel.selectedIndex].innerText;
		sel = document.getElementById('roleSelect');
		cell=row.insertCell();
		cell.innerHTML = sel.options[sel.selectedIndex].value;
		var button ="<button type=\"button\" class=\"btn btn-default\" onclick=\"removeTenant(" + (rowIndex-1) + ");\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;</button>";
		cell=row.insertCell();
		cell.innerHTML = button;
		loadTenantSelect(); 
	}
}

function removeTenant(rowindex) {
	var table = document.getElementById('userTenantsTable');
	table.deleteRow(rowindex+1); 
	// have to reset onclicks because row numbers have changed
	for (var i=1;i<(table.rows.length-1);i++) {
		var button ="<button type=\"button\" class=\"btn btn-default\" onclick=\"removeTenant(" + (i - 1) + ");\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;</button>";
		table.rows[i].cells[3].innerHTML=button;
	}
	loadTenantSelect();		
}

function loadTenantSelect() {
	// rather than hitting database again, snag list of available tenants from current tenant select
	// trimming out any tenants that are already in table
	var tenantList = document.getElementById('current-tenant');
	var table = document.getElementById('userTenantsTable');
	var options = '';
	for (i=0;i<tenantList.childElementCount;i++) {
		var rowExists=false;
		for (j=1;j<(table.rows.length-1);j++) {
			if (table.rows[j].cells[1].innerText==tenantList.options[i].innerText) {
				rowExists = true;
				break;
			}
		}				
		if (!rowExists) {
			options += '<option value="' + tenantList.options[i].value + '">' + tenantList.options[i].innerText + '</option>';
		}
	} 
	document.getElementById('tenantSelect').innerHTML = options;
	
}

function saveUserTenants() {
	var userid = getElementValue("currentUserId");
	// build array of existing tenants
	var data = {};
	data["id"]=userid;
	var table = document.getElementById('userTenantsTable');
	var tenants=[];
	for (var i=1;i<table.rows.length-1;i++) { // start on one to skip header row
		var tenant = {tenantid: table.rows[i].cells[0].innerText, 
					  role: table.rows[i].cells[2].innerText};
		tenants.push(tenant);
	}
	data["tenants"] = tenants;
	
	serviceUrl = "../service/user.php?action=setTenantAccess";
	postData(serviceUrl,data,"manageTenants-message","manageTenants-message",function(success) {
		// close dialog
		if (success) {
			$('#manageTenantsModal').modal('hide');
		}
	});	
}

function addTenant() {
	editTenant(0);
}

function editTenant(id) {
	
	editEntity(id,'tenant',prepareTenantEdit,'../');
}

function prepareTenantEdit(status) {
	
}

function saveTenant() {
	try {
		submitForm('tenantForm','tenant-message','tenant-message_text',false,'tenantid',onTenantSave);
		}
		catch(ex) {
			// do nothing for now - message set in submitForm
		}	
}

function onTenantSave(success) {

	if (success) {
		$('#tenantEditModal').modal('hide');
		loadTenants(offset);
	}
}

function deleteTenant(id) {
	alert('Tenant deletion is not yet supported.');
}

function resetPassword(id) {
	
	if (!confirm('Reset password: are you sure?')) {
		return;
	}
	
	var serviceURL = "../service/user.php";
	serviceURL += "?reset=true&id=" + id; 				
		
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	  
	 xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4) {
		  	if (xmlhttp.status==200) {
		  		alert('Password reset.');
				}
			else {
				alert('unable to reset password:' + xmlhttp.responseText);
				}
		    }
		 };
	xmlhttp.open("PUT",serviceURL,true);
	xmlhttp.send();	
}

function validatePasswords() {
	password1 = getElementValue("input-password");
	password2 = getElementValue("input-password2");
	if (password1!=password2) {
		setValidationState("user-passwordGroup","warning",true);
		setValidationState("user-password2Group","warning",true);
	}
	else {
		setValidationState("user-passwordGroup","success",true);
		setValidationState("user-password2Group","success",true);
	}
	
}

function loadTenants() {
		
	var template = "<table class=\"table table-striped table-hover table-responsive\">";
	template += "<thead><tr><th>Name</th><th>Actions</th></tr></thead>";
	template += "<tbody>{{#tenants}}"; 	
	template += "<tr><td><div class=\"user\"><span class=\"description\">{{name}}</span></div></td>";
	template += "<td><div class=\"btn-group btn-group-sm\" role=\"group\" aria-label=\"...\">";
	//template += "<a href=\"entityService.php?type=tenant&id={{id}}\">edit</a>";
	template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"editTenant({{id}});\"><span class=\"glyphicon glyphicon-pencil\"></span>&nbsp;</button>";
	//template += "<button type=\"button\" class=\"btn btn-default\" onclick=\"deleteTenant({{id}});\"><span class=\"glyphicon glyphicon-remove\"></span>&nbsp;</button>";
	template += "</div></td></tr>";
	template += "{{/tenants}}</tbody></table>";

	
	var serviceURL = "../service/entitiesService.php?type=tenant";
	serviceURL += '&offset=' + offset;
	var working = "Retrieving tenants . . .";
	var anchor = "tenantResultSpan";	
					
	if (working.length>0)
		{
		document.getElementById(anchor).innerHTML = working;
		}
		
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	  
	 xmlhttp.onreadystatechange=function() {
		  if (xmlhttp.readyState==4) {
		  	hideElement('loading');
		  	if (xmlhttp.status==200) {
			    var view = JSON.parse(xmlhttp.responseText);
				renderTemplate(template,view,anchor);
				var totalUsers=view.totalUsers;
				var numPage = Math.ceil(totalUsers/USERS_PER_PAGE);
				$('#page-selection').bootpag({total: numPage}).on("page",function(event,num) {
					offset = (num-1) * USERS_PER_PAGE;
					retrieveUsers(offset);
					});
				}
			else {
				document.getElementById(anchor).innerHTML = 'unable to load tenants:' + xmlhttp.responseText;
				}
		    }
		 };
	xmlhttp.open("GET",serviceURL,true);
	xmlhttp.send();
}
	

