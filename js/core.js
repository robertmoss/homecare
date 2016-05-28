
/* retrieves JSON from a web service 
 * Returns the parsed object retrieved from service
 */
function getJSON(serviceURL,anchor,working,callback,errorCallback)
		{
		if (working && working.length>0)
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
		  	if (xmlhttp.status==200) {
			    var obj = JSON.parse(xmlhttp.responseText);
				if (callback) {
					callback(obj);
				}
			}
			else if (errorCallback) {
					errorCallback(xmlhttp.responseText);
				}
		   }
		 };
		xmlhttp.open("GET",serviceURL,true);
		xmlhttp.send();
		}


/* retrieves JSON from a web service and renders it within specified anchor using 
 * the submitted mustache template
 * Returns too callback function count of rows retrieved, -1 if error
 */
function getAndRenderJSON(serviceURL,template,anchor,working,callback)
		{
		if (working && working.length>0)
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
		  if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		    var view = JSON.parse(xmlhttp.responseText);
		    var key;
		    if (!view.count) {
		    	key=view.id;
		    }
		    else {
		    	key =view.count;
		    }
			renderTemplate(template,view,anchor);
			if (callback) {
				callback(key);
			}
		   }
		 };
		xmlhttp.open("GET",serviceURL,true);
		xmlhttp.send();
		}
		
function renderTemplate(template,view,anchor,append) {
	
	if (template.length>0 && view && anchor.length>0) {
		var output = Mustache.render(template,view);
		if (!append) {
			document.getElementById(anchor).innerHTML = output;
		}
		else {
			document.getElementById(anchor).innerHTML += output;
		}
	}
}

/* retrieves markup from a web service and renders it within specified anchor 
 * callback is a function that will be executed once form is rendered
 * if used, it should have an status parameter which will be set to HTTP status code of respond (e.g. 200,400)
 */
function getAndRenderHTML(serviceURL,anchor,working,callback) {
		if (working && working.length>0)
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
		  		var status=xmlhttp.status;
		  		var markup = '';
		  		if (xmlhttp.status==200) {
		    		markup = xmlhttp.responseText;
		   			}
		   		else {
		   			markup = 'Unable to load form: ' + xmlhttp.responseText;
		   		}
		   		document.getElementById(anchor).innerHTML=markup;
		   		if (callback) {
		   			callback(status);
		   		}
		   	}
		 };
		xmlhttp.open("GET",serviceURL,true);
		xmlhttp.send();
	}

/*
 * Calls the specified service using delete method
 * If callback specified, calls it passing in the responseText from the service call
 */
function callDeleteService(serviceURL,workingDiv,workingMessage,callback) {
		if (workingDiv)
			{	
				if (!workingMessage || workingMessage.length==0) {
					workingMessage = "Deleting";
				}
				document.getElementById(workingDiv).innerHTML = workingMessage;
				showElement(workingDiv);
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
		 	if (workingDiv) {
		 		hideElement(workingDiv);
		 	}
			if (callback) {
				callback(xmlhttp.status,xmlhttp.responseText);
			}
		   }
		 };
		xmlhttp.open("DELETE",serviceURL,true);
		xmlhttp.send();
	}

function log(msg){
  if (window.console && console.log) {
    console.log(msg); //for firebug
  }
  //document.write(msg); //write to screen
  //$("#logBox").append(msg); //log to container
}


function validateForm(formID,messageDiv,messageSpan)
{
	// loops through form looking for required field indicators
	// returns true if form validates, false if there are errors
	
	var success=true;
	
	if(messageDiv && messageDiv.length>0) {
		hideElement(messageDiv);
	}
	
	var form = document.getElementById(formID);
	
	for (var i=0; i<form.length; ++i) {
		var field = form[i];
		if (field.name && field.type!='button') {
			if (field.className && field.className.indexOf('required')>0) {
				// we have a required field
				var x=1;
			}
		}
	}
	
	return success;
}

/*
 * Submits a form via AJAX, stringifying the data elements into a JSON document
 * URL for post should be on the form's action attribute
 *  idElement:	the HTML element to be set with the id coming back from the save
 *  callback(true/false) - optional callback function; will be called once form is submitted with true if success (200) or false otherwise
 */
function submitForm(formID,messageDiv,messageSpan,reloadOnSuccess,idElement,callback)
{
		
	if(messageDiv && messageDiv.length>0) {
		hideElement(messageDiv);
	}
	
	var form = document.getElementById(formID);
	var data = {};
	
	for (var i=0; i<form.length; ++i) {
		var field = form[i];
		if (field.name && field.type!='button') {
			if (field.type=='select-multiple') {
				// rip through options and create an array
				var arr = [];
				var opts = field.selectedOptions;
				for (var j=0;j<opts.length;j++) {
						var obj = {id:opts[j].value};
						arr.push(obj);
					}
				data[field.name] = arr;
			}
			else if (field.type=='checkbox') {
				data[field.name] = (field.checked) ? 'true':'false';
			}
			else {
				data[field.name] = field.value;
			}
		}
	}
	var request = new XMLHttpRequest();
	request.open(form.method,form.action,true);
	request.setRequestHeader('Content-Type','application/json; charset=UTF8');
	request.onreadystatechange=function() {
		 if (request.readyState==4) {
		 	var success;
		 	if (request.status==200) {
		 		var response = JSON.parse(request.responseText);
		 		if (idElement && idElement.length>0) {
		 			document.getElementById(idElement).value = response.id;
		 		}
		    	if(messageDiv && messageDiv.length>0) {
			    	 setMessage('Record (id=' + response.id + ') saved successfully.',messageDiv,messageSpan,true);
			    	}
			    if (reloadOnSuccess) {
			    	var url = window.location.href;
			    	if (url.indexOf("#")>0)
						{
							url=url.split("#")[0];
						}
					if (url.indexOf("?")>0)
						{
							url=url.split("?")[0];
						}
					url += "?id=" + response.id + "&mode=view";
		 			window.location = url;
			    	}
			    success=true;	
		    	}
		   	else {
		   		if(messageDiv && messageDiv.length>0) {
		   			setMessage('Save failed: ' + request.responseText,messageDiv,messageSpan,false);
		   		}
		   		success=false;
		   	}
		   	if (callback) {
		   		callback(success);
		   	}
		  }  
		};
	request.send(JSON.stringify(data));
}

/*
 * Converts the specified data (object graph) into JSON and 
 * posts as payload to the specified service via AJAX calls
 */
function postData(serviceUrl,data,messageDiv,messageSpan,callback)
{
		
	if(messageDiv && messageDiv.length>0) {
		hideElement(messageDiv);
	}
	
	var request = new XMLHttpRequest();
	request.open("POST",serviceUrl,true);
	request.setRequestHeader('Content-Type','application/json; charset=UTF8');
	request.onreadystatechange=function() {
		 if (request.readyState==4) {
		 	var success;
		 	if (request.status==200) {
		 		if (request.responseText.length>0) {
			 		var response = JSON.parse(request.responseText);
			 		if(messageDiv && messageDiv.length>0) {
				    	 setMessage('Record (id=' + response.id + ') saved successfully.',messageDiv,messageSpan,true);
				    	}
				   }
			    success=true;	
		    	}
		   	else {
		   		if(messageDiv && messageDiv.length>0) {
		   			setMessage('Save failed: ' + request.responseText,messageDiv,messageSpan,false);
		   		}
		   		success=false;
		   	}
		   	if (callback) {
			   	callback(success);
			   }
		  }  
		};
	request.send(JSON.stringify(data));
}

function setMessage(message, container, zone, success) {
	// find specified message zone, unhide it, and set message text in containter element
	
	// defaults if just message specified
	if (!container) {
		container = 'message';
		}
	if (!zone) {
		zone = 'message_text';
	}
	
	var zoneElement = document.getElementById(zone);
	var containerElement = document.getElementById(container);
	zoneElement.innerHTML = message;
	containerElement.className = 'alert';
	if (success) {
		containerElement.className += ' alert-success';
	}
	else
	{
		containerElement.className += ' alert-danger';
	}
	showElement(container);
}

function addAlert(message,parentid,alerttype,duration) {
	
	// alert type: success, info, warning, error
	// duration: time in milliseconds to display alert before removing it
	
	if(alerttype=='error') {alerttype='danger';}

    $('#'+parentid).append('<div id="alertdiv" class="alert alert-' +  alerttype + '"><a class="close" data-dismiss="alert">×</a><span>'+message+'</span></div>');

	if (duration && duration>0)
	    setTimeout(function() { // close the alert and remove this if the users doesnt close it in 5 secs
	      $("#alertdiv").remove();}, duration);
	     
  }


function toggleElement(ID) {
	var element = document.getElementById(ID);
	if (element) {
		element.style.display = (element.style.display=="block")?"none":"block";
	}
}

function showElement(ID) {
	var element = document.getElementById(ID);
	if (element) {
		element.className = element.className.replace("hidden","");
	}
}

function hideElement(ID) {
	var element = document.getElementById(ID);
	if (element) {
		if(element.className.indexOf("hidden")==-1) {
			element.className += " hidden";
		}
	}
}

function setFocus(ID) {
	var element = document.getElementById(ID);
	element.focus();
}

function setElementValue(ID,text) {
	var element = document.getElementById(ID);
	if (element) {
		element.value = text;
	}
}

function getElementValue(ID) {
	var element = document.getElementById(ID);
	var value ='';
	if (element) {
		value = element.value;
	}
	return value;
}

function setElementHTML(ID,html) {
	var element = document.getElementById(ID);
	if (element) {
		element.innerHTML = html;
	}
}

function setElementText(ID,text) {
	var element = document.getElementById(ID);
	if (element) {
		element.innerText = text;
	}
}

function disableElement(ID) {
	var element = document.getElementById(ID);
	if (element) {
		element.disabled = true;
	}
}

function enableElement(ID) {
	var element = document.getElementById(ID);
	if (element) {
		element.disabled = false;
	}
}

function setValidationState(ID,state,feedback) {
	// just pass in success, warning, or error. Caller doesn't need to know Bootstrap classnames
	// if feedback is specified true, a feedback icon with be added
	var element = document.getElementById(ID);
	if (element) {
		// remove all Bootstrap validate state closses
		element.className = element.className.replace("has-success","");
		element.className = element.className.replace("has-warning","");
		element.className = element.className.replace("has-error","");
		
		element.className+=" has-" + state.toLowerCase();
		if (feedback) {
			element.className += " has-feedback";
			
			// need to set feedback icon: loop through child elements looking for span with form-control-feedback
			for(var i=0;i<element.childNodes.length;i++) {
				var child = element.childNodes[i];
				if (child.className && child.className.indexOf("form-control-feedback")>0) {
					child.className = child.className.replace('glyphicon-ok','');
					child.className = child.className.replace('glyphicon-warning-sign','');
					child.className = child.className.replace('glyphicon-remove','');
					
					var newGlyph = ' glyphicon-' + (state.toLowerCase()=='success' ? 'ok' : (state.toLowerCase()=='warning' ? 'warning-sign' : 'remove'  ) );
					child.className += newGlyph;
									
				}
					
				
			}
			
		}
	}
}


// ------------ form handling functions ---------------
function getAndRenderForm(serviceURL,formID,idPrefix,callback) {
	// retrieves JSON object from specified service and uses it to populate fields on a form
	// serviceURL:	URL of service to call
	// formID:		ID of the form to be populated
	// idPrefix:	the prefix appended to IDs of form elements (e.g. if name field is "txt_name", prefix is "txt_")			
	// callback:	a function to call when form has been rendered (or error) - will callback with success (boolean) and message(string) as parameters:
	//					e.g. callback(success,message)		
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
			    var view = JSON.parse(xmlhttp.responseText);
				renderForm(formID,view,idPrefix);
				if (callback) {
					callback(true,'success');
				}
			}
			else {
				if (callback) {
					callback(false,xmlhttp.responseText);
				}
			}	
		   }
		 };
	xmlhttp.open("GET",serviceURL,true);
	xmlhttp.send();
	}

function renderForm(formID,view,idPrefix) {
	for(var property in view) 
		{
			targetId = idPrefix + property;
			var element = document.getElementById(targetId);
			if (element) {
				element.value = view[property];
			}
		}
}

// ------------- string management functions

function escapeSingleQuotes(string) {
	var out = string.replace(/'/g, '\\\'');
	return out;
}

function ucfirst(str) {
	// capitalizes first character of a string
	return capitalized = str.charAt(0).toUpperCase() + str.substring(1);	
}



// ------------ Google Maps API helper functions ---------------
var APIKey = "AIzaSyB9Zbt86U4kbMR534s7_gtQbx-0tMdL0QA";
var markers = []; // persists marker set from map 

function dropMarker(map,pos,title,icon,id) {
	
	icon = (typeof icon === "undefined")
		? "http://maps.google.com/mapfiles/ms/icons/red-dot.png"
		: icon;
		
	var marker = new google.maps.Marker(
		{position: pos,
		 map: map,
		 title: title,
		 animation: google.maps.Animation.DROP,
		 icon: icon		 
		});
	
	markers[id] = marker;
	return marker;
}

function clearMarkers()
{
	if (markers.length>0) {
		for (var i = 0; i < markers.length; i++) {
    		if (markers[i]) {
    			markers[i].setMap(null);
    			}
			}
		}
	markers = [];	
}

function addInfoWindow(marker, contentString, clickLink) {
	
	var infoWindow = new google.maps.InfoWindow({
		content: contentString}
		);
	google.maps.event.addListener(marker,'mouseover',function() {
		infoWindow.open(map,marker);
		});
	google.maps.event.addListener(marker,'mouseout',function() {
		infoWindow.close();
		});
	if (clickLink && clickLink.length>0) {
			google.maps.event.addListener(marker,'click',function() {
				window.location = clickLink;
			});
		}
}

function addInfoWindowOnClick(map, marker, contentString) {
		
		google.maps.event.addListener(marker,'click',function() {
			if (infoWindow) {
				// close window if open
				infoWindow.close();
			}
			infoWindow = new google.maps.InfoWindow({content: contentString});
			infoWindow.open(map,marker);
			});
}	

function addInfoWindowOnHover(map, marker, contentString) {
		
		google.maps.event.addListener(marker,'mouseover',function() {
			if (infoWindow) {
				// close window if open
				infoWindow.close();
			}
			infoWindow = new google.maps.InfoWindow({content: contentString});
			infoWindow.open(map,marker);
			});
}

// resizes Google map to show the specified point
function resizeMap(map,point)
{
    var bounds = new google.maps.LatLngBounds(map.getCenter());
    bounds.extend(point);
  	map.fitBounds(bounds);
    
}

function geocodeAddress(address)
{
	
	/*if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
	else
		{// code for IE6, IE5
		 xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		 }
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		    var json = JSON.parse(xmlhttp.responseText);
		    }
		  }
    var serviceURL = "http://maps.googleapis.com/maps/api/geocode/json?";
	xmlhttp.open("GET",serviceURL,true);
	xmlhttp.send();*/
	alert(APIKey);	
}


/* Entity Functions */
function editEntity(id,entity,callback,relative_path) {
	
	// loads and fills modal form for editing a dataentity
	// see admin.php tenant form for calling model
	
	var headerText= (id>0) ? 'Edit ' + entity : "Add New " + entity;
	setElementText(entity + 'Header',headerText);
	if (!relative_path) {
		relative_path='';
	}
	var serviceURL = relative_path + "service/formService.php?type=" + entity;
	serviceURL += "&id=" + id;
	
	getAndRenderHTML(serviceURL,entity + 'FormAnchor','',callback);
	hideElement(entity+"-message");
	$('#' +entity + 'EditModal').modal();
	
}

function saveEntity(entity,callback) {
	
	// submits a form using the 
	
	try {
		var form = entity + 'Form';
		var message = entity + '-message';
		var message_text = entity + '-message_text';
		var id = entity + 'id';
		submitForm(form,message,message_text,false,id,callback);
		}
		catch(ex) {
			// do nothing for now - message set in submitForm
		}	
}


function deleteEntity(entity,id,working,callback) {

	// deletes entity using standard Entity Service delete method
	// if callback is specified, should have success parameter, which will be set to true if delete succeeds false otherwise
	
	var serviceURL = "service/entityService.php?type=" + entity;
	serviceURL += "&id=" + id;
	
	callDeleteService(serviceURL,working,'Deleting',function(status,text) {
		var success=(status==200);
		if (callback) {
			callback(success,text);
		}
	});

}

function addChildEntity(sourceSelect,destinationSelect) {
	var opt = document.createElement("option");
	opt.text = sourceSelect.selectedOptions[0].text;
	opt.value =  sourceSelect.selectedOptions[0].value;
	opt.selected=true;
	destinationSelect.add(opt);
}


function createChildEntity(entity,service_path) {

	editChildEntity(entity,0,service_path);
}

function editChildEntity(entity,id,service_path) {
	
	var header = (id==0) ? 'Add ' : 'Edit ';
	header += entity;
	setElementHTML('childEditHeader',header);

	document.getElementById('childType').value = entity;
	hideElement('childMessageDiv');
	
	// need to close entity edit modal - multiple open modals not supported by bootstrap
	selector = "#" + document.getElementById('type').value + "EditModal";
	$(selector).modal('hide');
	
	$("#childEditModal").modal({
 	   backdrop: 'static',
    	keyboard: false
		});
	var parentType = document.getElementById('type').value;
	var entityId = document.getElementById(parentType + 'id').value;
	if (!service_path) {
		service_path='service';
	}
	var serviceURL = service_path + "/formService.php?type=" + entity;
	serviceURL += "&id=" + id;
	serviceURL += "&parentid=" + entityId;
	
	getAndRenderHTML(serviceURL,'childEditContainer','',prepareChildEdit);

}

function deleteChildEntity(entity,id) {
	
	var serviceURL = "service/entityService.php?type=" + entity;
	serviceURL += "&id=" + id;
	var working = "workingDelete" + id;
	
	callDeleteService(serviceURL,working,'Deleting',function(status,text) {
		if (status==200) {
			// reload form to remove deleted child entity
			entity = document.getElementById('type').value;
			id = document.getElementById(entity + 'id').value;

			editEntity(id,entity);
		}
		else {
			alert('Unable to delete ' +entity+ ': ' + text);
		}
	});

}

function prepareChildEdit(status) {
	hideElement('childEditLoading');
	document.getElementById('childEditSaveButton').disabled=false;
}

function saveChild() {
	var entity=document.getElementById('childType').value;
	var formid=entity + 'Form';
	var idfield = entity + 'id';
	submitForm(formid,'childMessageDiv','childMessageSpan',false,idfield,childSaveComplete);	
}

function cancelChild() {
	
	$("#childEditModal").modal('hide');

	// need to re-open edit modal
	selector = "#" + document.getElementById('type').value + "EditModal";
	$(selector).modal('show');

}

function childSaveComplete(success) {
	if (success) {
		
		$("#childEditModal").modal('hide');

		var entity=document.getElementById('childType').value;
		var id=document.getElementById(entity+'id').value;
		var name='newly added ' + entity;
		var nameInput = 'txt' + entity.charAt(0).toUpperCase() + entity.slice(1) + 'Name';
		var textbox = document.getElementById(nameInput); // this assumes all entities have a name
		if (textbox) {
			name = textbox.value;
		}
		
		var selectBox = document.getElementById(entity+'Select');
		if (selectBox) { // we have a linkedentity: set values for newly added child into select
			var opt = document.createElement("option");
			opt.text = name;
			opt.value =  id;
			opt.selected=true;
			selectBox.add(opt);
		}
		else {
			alert('need to add logic to update childentities table.');
		}
		
		// reopen edit modal for parent (which has been hiding patiently in the background)
		var parententity = document.getElementById('type').value;
		var parentid = document.getElementById(entity + 'id').value;
		$('#' + parententity + 'EditModal').modal();

	}
}

