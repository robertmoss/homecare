$(document).ready(function() {
	$('#entityForm').validator({
		delay: 100, 
		disable: true, 
		feedback: {
  			success: 'glyphicon-ok',
  			error: 'glyphicon-remove'
			}
	});
	
	$('#entityForm').validator().on('submit', function (e) {
  		if (!e.isDefaultPrevented()) {
  			e.preventDefault(); // don't actually submit form; we submit via AJAX
  			saveEntity();
  			}
 
		});
	
	// this is hardwired to provide special handling for image strip—pretty hacky, but not sure where else to put it yet
	var type=document.getElementById('type').value;
	var mode=document.getElementById('mode').value;
	if (type=='location' && mode!='edit') {
		loadMediaForLocation(document.getElementById('id').value);
	}
});

function isEditable() {
	// returns true if user is able to edit the current entity; we depend upon present of edit button (set Server side) for that
	return ($('#editEntity').length>0);
}

function loadMediaForLocation(locationid) {
	serviceUrl = 'service/entitiesService.php?type=media&locationid=' + locationid;
	getAndRenderJSON(serviceUrl, getImageStripTemplate(isEditable()),'imageStrip');
}

function editMedia(mediaid) {
	
	document.getElementById('childEditHeader').innerText = 'Edit Media';
	hideElement('childMessageDiv');
	document.getElementById('childType').value="media"; 
	$('#childEditModal').modal();
	
	var serviceURL = "service/formService.php?type=media";
	serviceURL += "&id=" + mediaid;
	
	getAndRenderHTML(serviceURL,'childEditContainer','',prepareEdit);
}

function prepareEdit(status) {
	hideElement('childEditLoading');
	document.getElementById('childEditSaveButton').disabled=false;
}

function deleteMedia(mediaid) {
	
	showModalDialog('Are you sure you want to permanently delete this media file?','Delete Image File','OK','Cancel',
		function() {
			showWorkingPanel('Deleting file...','media'+mediaid,true);
			var serviceURL = "service/entityService.php?type=media&id=" + mediaid;
			callDeleteService(serviceURL,null,deleteMediaCallback);
		}
		);
}

function deleteMediaCallback(status,response) {
	var message;
	var result;
	if (status==200) {
		message="Media file successfully deleted.";
		result="success";
	}
	else {
		message = "Unable to delete media fie: " + response;
		result='error';
	}
	showWorkingPanelResults(message,result,"imageStrip",false);
	locationid = $("#id").val();
	loadMediaForLocation(locationid);
}

function setMediaAsPrimary(id) {
	var serviceUrl = 'service/entityService.php?type=location&action=fieldUpdate';
	var imageurl = document.getElementById('media'+id).getElementsByTagName("img")[0].src;
	var locationid = document.getElementById('locationid').value;
	var data = {
		id: locationid,
		imageurl: imageurl
	};
	postData(serviceUrl,data,null,null,function(success) {
		if (success) {
			document.getElementById('primaryImage').src=imageurl;
		}
	});
}


function submitSubForm(formID,formDiv,messageDiv,messageSpan,selectID)
{
	if(messageDiv && messageDiv.length>0) {
		hideElement(messageDiv);
	}
	
	var form = document.getElementById(formID);
	var data = {};
	var name = '';
	
	for (var i=0; i<form.length; ++i) {
		var field = form[i];
		if (field.name && field.type!='button') {
			data[field.name] = field.value;
			if (field.name=='name') {
				// sock away for populating select option on main form
				name = field.value;
			}
		}
	}
	
	var request = new XMLHttpRequest();
	request.open(form.method,form.action,true);
	request.setRequestHeader('Content-Type','application/json; charset=UTF8');
	request.onreadystatechange=function() {
		 if (request.readyState==4) {
		 	if (request.status==200) {
		 		var response = JSON.parse(request.responseText);
		 		document.getElementById('id').value = response.id;
		    	if(messageDiv && messageDiv.length>0) {
			    	 setMessage('Record (id=' + response.id + ') saved successfully.',messageDiv,messageSpan,true);
			    	}
			    // add new value to select and set selected
			    var select = document.getElementById(selectID);
			    var option = document.createElement("option");
			    option.value = response.id; 
			    option.text = name;
			    option.selected = true;
			    select.add(option);
			    // close modal form
				hideElement(formDiv);
		    	}
		   	else {
		   		if(messageDiv && messageDiv.length>0) {
		   			setMessage('Save failed: ' + request.responseText,messageDiv,messageSpan,false);
		   		}
		   	}
		  }  
		};
	request.send(JSON.stringify(data));
}

function addSubEntity(divid) {
	showElement(divid);
}

function setMode(mode) {
	var type = document.getElementById('type').value;
	var id = document.getElementById('id').value;
	if (mode=='edit')
		{
		var returnURL = 'entityPage.php?type=' + type + '&id=' + id + '&mode=view';
		var newURL = 'entityPage.php?type=' + type + '&id=' + id + '&mode=edit&return=' + encodeURIComponent(returnURL);
		window.location = newURL;
		}
	else if(mode=='view') {
		var id = document.getElementById("id").value;
		var newURL;
		if (id==0) {
			newURL = "index.php";
		}
		else {
			var newURL = window.location.href.split('?')[0] + "?type=" + type +"&id=" + id + "&mode=view";
		}
		window.location = newURL;		
	}
}

function saveEntity() {
	
	// check to see whether there is an image upload control on page. If there is, upload image first and get its new URL
	var imageUploader = document.getElementById('uploadImage');
	if (imageUploader) {
		// this is still a work in progress
		
		var form = document.getElementById('uploadImage');
		var fileInput = document.getElementById('fileUpload');
		var file = fileInput.files[0];
		var request = new XMLHttpRequest();
		var boundary = '----WebKitFormBoundaryGdDCLBKdAZ8t8lO3';
		request.open(form.method,form.action,true);
		//request.setRequestHeader('Content-Type','multipart/form-data; boundary=' + boundary);
		//request.setRequestHeader('Content-Type',false);
		request.onreadystatechange=function() {	
			if (request.readyState==4) {
			 	if (request.status==200) {
			 		var response = JSON.parse(request.responseText);
			 		alert(response.url);
			   	}
			   	else {
			   		// error saving entity
			   		alert(request.responseText);
			   		}  
				}
			};		
		request.send(file);
		}
	
	submitForm('entityForm','messageDiv','messageSpan',false,'id',afterSave);

}

function afterSave(success) {
	// invoked as callback from submitForm
	if (success) {
		// need to reload page with new entity or redirect to return Url, if specifiedf
		var url;
		var returnUrlElement = document.getElementById('returnUrl');
		if (returnUrlElement && returnUrlElement.value.length>0) {
			url = returnUrlElement.value;
		}
		else {
			var id = document.getElementById('id').value;
			var type = document.getElementById('type').value;
			url = 'entityPage.php?type=' + type + '&id=' + id + '&mode=view';
		}
		window.location = url;
	}
}



