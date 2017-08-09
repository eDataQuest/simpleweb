var XEditPath = '/XEdit.json';

$(document).ready(function ()
{
  $.fn.editableform.template =
          '<form class="form editableform" >' +
          '<div class="control-group">' +
          '<div class="input-group col-xs-12">'+
          '<div class="editable-input" ></div>'+
          '<div class="input-group-addon" style="border: none; padding: 0px; background-color: inherit;">'+
          '<div class="editable-buttons "></div>'+
          '</div>' +
          '</div>' +
          '<div class="editable-error-block"></div>' +
          '</div>' +
          '</form>';


  $.fn.editableform.buttons =
    '<button type="submit" class="btn btn-primary btn-xs editable-submit"><i class="glyphicon glyphicon-ok"></i></button>'+
    '<button type="button" class="btn btn-default btn-xs editable-cancel"><i class="glyphicon glyphicon-remove"></i></button>';

  // Startup the edit
  $('.editable').editable(
  {
    mode: 'inline',
    onblur: 'submit',
    inputclass: 'input-md masked',
    url: XEditPath,
    emptytext: '&nbsp;',
    success: function (data, newValue)
    {
      if (data)
      {
        if (data.rc == 1)
        {
          // ok No Problems found from server validation...
        }
        else
        {
          // See if anyone needs to be un-set before sending the error message....
          var name = $(this).data('name');
          $('.editable-open').each(function (index)
          {
            if (name != $(this).data('name'))
            {
              $(this).editable('hide');
            }
          });
          return data.msg;
        }
      }
    }
  });

  $('.editable').on('save', function(e, p) {
    // this is a hack but it works
    if (p.response)
    {
      if (p.response.hasOwnProperty('dispValue'))
      {
        timeoutA = this;
        timeoutB = p.response.dispValue;

        if ($(this).data('type') != 'select')
        {
          setTimeout(function(){
            if (timeoutB === null)
            {
              $(timeoutA).html('&nbsp;');
            }
            else
            {
              $(timeoutA).text(timeoutB);
            }
          }, 50);
        }
      }
    }
  });

  $('.editable').on('shown', function()
  {
    var h = $(this).data('editable').$element[0];
    var df = ".editable[data-form='"+$(this).data('form')+"'][data-hide!='true'][style!='display:none;']";

    // Do input masking
    var mask      = $(this).data('mask');
    var maskExtra = $(this).data('mask-extra');
    if (mask != null)
    {
      if (maskExtra !== null)
      {
        console.log(maskExtra);
        $(this).data('editable').input.$input.mask(mask, maskExtra);
      }
      else
      {
        $(this).data('editable').input.$input.mask(mask);
      }
    }

    // Make Tab & Enter Keys work.
    $(this).data('editable').input.$input.on('keydown', function(e)
    {
      if ((e.which == 9) || (e.which == 13))
      {
        // On keydown, we look for a tab
        e.preventDefault();

        if ($(df).length > 1)
        {
          var idx = $(df).index(h);

          // We hide and then show down here to handle the can't-tab-after-error issue, which is actually
          // just a editor-already-showing issue.

          // Go backwards for shift-tab and shift-enter.
          if (e.shiftKey)
          {
            idx--;
            if (idx >= 0)
            {
              $(df).eq(idx).editable('hide');
              $(df).eq(idx).editable('show');
            }
            else
            {
              $(df).last().editable('hide');
              $(df).last().editable('show');
            }
          }
          else
          {
            idx++;
            if (idx >= $(df).length)
            {
              $(df).first().editable('hide');
              $(df).first().editable('show');
            }
            else
            {
              $(df).eq(idx).editable('hide');
              $(df).eq(idx).editable('show');
            }
          }
        }
      }
    });
  });

  $('[data-button-type="add"]').click(function (e)
  {
    var the_button = this;

    if ($('.editable-open').length)
    {
      // fire the add after the input field is closed so data is passed correctly
      $('.editable-open').on('save', function ()
      {
        doCreate(the_button);
      });

      // Fire the add if input is opened but no change in input
      if ($('.editable-open').text().trim() == $('.editable-input input').val().trim())
      {
        doCreate(the_button);
      }
    }
    else
    {
      // Fire the add if no input is open
      doCreate(the_button);
    }
  });

  $('[data-button-type="done"]').click(function ()
  {
    if ($('.editable-open').length)
    {
      // fire the done AFTER the input field is closed so data is passed correctly
      $('.editable-open').on('save', function () {
        window.location.href = $('[data-button-type="done"]').data("redirect");
      });
      // Fire the done if input is opened but no change in input
      if ($('.editable-open').text().trim() == $('.editable-input input').val().trim())
      {
         window.location.href = $('[data-button-type="done"]').data("redirect");
      }
    }
    else
    {
      // fire the done is no input is open
      window.location.href = $(this).data("redirect");
    }
  });

  $('[data-button-type="delete"]').click(function ()
  {
    var x = confirm("Are you sure you want to delete?");
    if (x)
    {
      $.getJSON({
        url: XEditPath,
        data: {
          action:   "delete",
          table:    $(this).data("table"),
          pk:       $(this).data("pk"),
          redirect: $(this).data("redirect")
        },
        success: function (data) {
          if (data.rc == 1)
          {
            window.location.href = data.redirect;
          } else
          {
            $('#form_message_'+$(this).data("form")).text(data.msg);
          }
        }
      });
    }
  });
  // END OF DOCUMENT READY...
});

function doCreate(selector)
{
  var formData =
  {
    action:   'create',
    table:    $(selector).data("table"),
    redirect: $(selector).data("redirect"),
    route:    $(selector).data("route")
  };

  doSave(selector, formData);
}

function doFullUpdate(selector)
{
  var formData =
  {
    action:   'fullupdate',
    table:    $(selector).data("table"),
    redirect: $(selector).data("redirect"),
    route:    $(selector).data("route"),
    pk:       $(selector).data("pk")
  };

  doSave(selector, formData);
}

function doSave(selector, formData)
{
  $(".editable[data-form='"+$(selector).data('form')+"']").editable('submit',
    {
      url: XEditPath,
      data: formData,
      ajaxOptions: {
        dataType: 'json'
      },
      success: function (data, config)
      {
        if (data.rc == 1)
        {
          $('#googleModal').modal('hide');
          if (formData.action == 'create')
          {
            window.location.href = data.redirect;
          }
        }
        else
        {
          if(data.field)
          {
            $(".editable[data-form='"+$(selector).data('form')+"'][data-name='"+data.field+"']").editable('show');
            $('.editable-error-block').parent().addClass('has-error');
            $('.editable-error-block').css('display', 'block').text(data.msg);
          }
          else
          {
            $('#form_message_'+$(selector).data("form")).text(data.msg);
          }
        }

        if (typeof editableOnSaveSuccessHook == 'function')
        {
          editableOnSaveSuccessHook(data);
        }
      },
      error: function (errors)
      {
        $('#form_message_'+$(selector).data("form")).text('Network Error!');

        if (typeof editableOnSaveErrorHook == 'function')
        {
          editableOnSaveErrorHook(errors);
        }
      }
    });
}