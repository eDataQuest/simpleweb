function addChangeHandlerToPairedCheckboxes()
{
  // Add our event handler to any xeditable paired checkboxes
  $('input.editable-paired[type="checkbox"]').change(function() {
    var formSelect = '[data-form="' + $(this).data('paired-form') + '"]';
    var sel = '[data-name="' + $(this).data('paired-control') + '"]';

    // Update the hidden control corresponding to this checkbox
    if ($(this).is(':checked'))
    {
      $(formSelect + sel).editable('setValue', 1);
    }
    else
    {
      $(formSelect + sel).editable('setValue', 0);
    }

    // If we are saving on change, do so now, because x-edit won't do it for us from a setval
    if ($(formSelect + '[data-saveonchange]').data('saveonchange'))
    {
      $(formSelect + sel).editable('submit');
    }

  });
}

$(document).ready(function()
{
  addChangeHandlerToPairedCheckboxes();
});

