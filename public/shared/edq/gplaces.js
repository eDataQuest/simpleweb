window.googleReady     = false;
window.qFormId         = null;
window.qGoogleFullName = null;
window.prevNameVal     = null;
var autocomplete       = null;

$(document).ready(function()
{
  // Initialize the modal
  $('#googleModal').modal({
    // close modal if 'esc' key pressed.
    keyboard: true,
    // do not show immediately after creation.
    show: false
  });

  // Click handler for the "accept this place" button.
  $('#modalAcceptButton').click(function()
  {
    $('.modalAlertHolder').html('');
    populateFormFromGPlace();

    if (!$('[data-form="' + window.qFormId + '"][data-saveonchange]').data('saveonchange'))
    {
      $('#googleModal').modal('hide');
    }
  });

  // Event handler that fires after the dialog is shown.
  $('#googleModal').on('shown.bs.modal', function(e) {
    $('#googleModalInput').focus();
  });

  // Event handler that fires after the dialog is hidden.
  $('#googleModal').on('hidden.bs.modal', function(e) {
    $('.modalAlertHolder').html('');
  });

  // Event handler that fires when the x-editable is shown
  $('.editable').on('shown', function()
  {
    if ('1' == $(this).data('places'))
    {
      // Whenever an editable is shown with 'places' set, create a handler for the
      // google places search button.
      window.qFormId = $(this).data('form');
      showGoogleModal();
    }
  });
});

// generator.js will call this whenever the editable 'save' function (doSave) returns success.
function editableOnSaveSuccessHook(data)
{
  /*
    The "problem" with this section now is that it's always called, for every field, so we
    need a way to only do this stuff if this hook is in response to our modal.  Otherwise,
    at best, we're needlessly manipulating invisible dom elements.

    At worst, we may end up storing the wrong piece of data then using it later.
  */
  if (data.rc == 1)
  {
    $('.modalAlertHolder').html('');
  }
  else
  {
    var formSelect  = '[data-form="' + window.qFormId + '"]';

    $(formSelect+'[data-name="name"]').editable('setValue', prevNameVal);

    $('.modalAlertHolder').html(
      '<div class="alert alert-danger">' +
      data.msg +
      '</div>'
    );
  }
}

function showGoogleModal()
{
  if (window.googleReady)
  {
    var formSelect  = '[data-form="' + window.qFormId + '"]';
    $(formSelect+'[data-name="name"]').editable('hide');
    $('#googleModalInput').val($(formSelect+'[data-name="name"]').editable('getValue', true));
    $('#googleModal').modal('show');
  }
}

function populateFormFromGPlace()
{
  if (window.qFormId != null)
  {
    var componentForm = {
      street_number: 'short_name',
      route: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'short_name',
      country: 'long_name',
      postal_code: 'short_name'
    };

    var place       = autocomplete.getPlace();
    var formSelect  = '[data-form="' + window.qFormId + '"]';
    var street_num  = '';
    var street_name = '';
    var city        = '';
    var state       = '';

    if (place != null)
    {
      if (place.address_components != null)
      {
        for (var i = 0; i < place.address_components.length; i++)
        {
          var addressType = place.address_components[i].types[0];
          if (componentForm[addressType])
          {
            var val = place.address_components[i][componentForm[addressType]];
            switch (addressType)
            {
              case 'street_number':
                street_num = val;
              break;

              case 'route':
                street_name = val;
              break;

              case 'locality': // city
                $(formSelect+'[data-name="city"]').editable('setValue', val);
              break;

              case 'administrative_area_level_1': // state
                $(formSelect+'[data-name="state"]').editable('setValue', val);
              break;

              case 'postal_code': // zip
                $(formSelect+'[data-name="zip"]').editable('setValue', val);
              break;
            }
          }
        }

        $(formSelect+'[data-name="address_1"]').editable('setValue', street_num + ' ' + street_name);
      }
    }

    var nameFromForm = $('#googleModalInput').val();
    var usedName = nameFromForm;

    if (window.qGoogleFullName != null)
    {
      usedName = window.qGoogleFullName == nameFromForm ? place.name : nameFromForm;
    }

    window.prevNameVal = $(formSelect+'[data-name="name"]').editable('getValue', true);
    $(formSelect+'[data-name="name"]').editable('setValue', usedName);

    if ($(formSelect+'[data-saveonchange]').data('saveonchange'))
    {
      doFullUpdate($(formSelect+'[data-saveonchange]')[0]);
    }
  }
}

function initAutocomplete()
{
  window.googleReady = true;
  $(document).ready(function()
  {
    autocomplete = new google.maps.places.Autocomplete(document.getElementById('googleModalInput'));
    autocomplete.addListener('place_changed', fillInAddress);
  });
}

function fillInAddress()
{
  window.qGoogleFullName = $('#googleModalInput').val();
}
