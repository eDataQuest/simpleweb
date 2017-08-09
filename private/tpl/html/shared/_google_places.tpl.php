<?php
/*
  CSS to ensure the google places list is on top of the modal.  The default values are:

  backdrop fadein: 1040
  modal: 1050
  google container: 1000

  So we adjust the google container to be 2000.
 */
?>
<style type="text/css">.pac-container { z-index: 2000 !important; }</style>

<div class="modal fade" id="googleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Search for a Place</h4>
      </div>
      <div class="modal-body">
        <div class="modalAlertHolder"></div>
        <input type="text" class="form-control" placeholder="Search for a Location" id="googleModalInput"/>
        <p></p>
        <h3>How to find a new Address</h3>
        <p>
          <b>Type in the name of the service location</b> (and adddress if necessary) and then click the one you want in the dropdown box
          listed below.  Once you are happy with with your selection <b>press Accept Place</b> to lock in your selection.
        </p>
        <h3>How to Correct a Service name</h3>
        <p>
          <b>Just type over the name</b> to make it say what you want then
          l <b>press Accept Place</b> to lock in your change.
        </p>

        <h3>Did you get here by mistake?</h3>
        <p>
          No need to worry just <b>press the Close Button</b> to go back.
        </p>

      </div>
      <div class="modal-footer">
        <button type="button" id="modalCancelButton" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" id="modalAcceptButton" class="btn btn-primary">Accept place</button>
      </div>
    </div>
  </div>
</div>
