                    <div id="childEditModal" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <!-- Modal content-->
                            <div class="modal-content">
                              <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                <h4 id="childEditHeader" class="modal-title">Modal Header</h4>
                                <input id="childType" type="hidden" value=""/>
                              </div>
                              <div id="childEditBody" class="modal-body">
                                <div id="childEditLoading" class="ajaxLoading">
                                </div>
                                <div id="childEditContainer" class="container-fluid">
                                    <p></p>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <div id="childMessageDiv" class="message hidden">
                                    <span id="childMessageSpan"><p>Your message here!</p></span>
                                </div>
                                <button type="button" class="btn btn-default" onclick="cancelChild();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Cancel</button>
                                <button id="childEditSaveButton" type="button" class="btn btn-primary" onclick="saveChild();" disabled>
                                    <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Save
                                </button>
                              </div>
                           </div>
                        </div>
                    </div>  