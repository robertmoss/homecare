/*
 * modalDialog.js: include on any pages that uses the modalDialog partial for modal dialog box support
 */

var yesCallback;

function showModalDialog(message,title,yestext,notext,callback) {
	
	if (title) {
		setElementHTML('modalDialogHeader',title);
	}
	if (message) {
		setElementHTML('modalDialogBody',message);
	}
	if (yestext) {
		setElementText('modalDialogYes',yestext);
	}
	if (notext) {
		setElementText('modalDialogNo',notext);
	}
	yesCallback = callback;
	$('#modalDialog').modal();
}

function modalYes() {
	$('#modalDialog').modal('hide');
	if (yesCallback) {
		yesCallback();
	}
}
