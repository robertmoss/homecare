<!-- include this panel to have an in-page (vs modal) working display
    Three modes:
        Working: shows working message and graphic while job is executing
        Success: shows results of successful operation
        Error: shows results of warning operation
        Include workingPanel.js along with this partial to use
-->
        <div id="workingPanel" class="alert alert-dismissable alert-info hidden">
            <button type="button" class="close" aria-label="Close" onclick="hideElement('workingPanel');return(false);">
                <span aria-hidden="true">&times;</span>
            </button>
            <div id="workingPanelMessage">Working message goes here.</div>
            <div id="workingPanelIcon">
                <img src="img/icons/ajax-loader.gif" />
            </div>
        </div>
