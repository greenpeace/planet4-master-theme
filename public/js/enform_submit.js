/* global en_vars, google_tag_value, dataLayer */

const p4_enform_frontend = (function ($) {

  const enform = {};

  enform.getFormData = function() {
    let supporter = {
      questions: {}
    };

    // Prepare the questions/optins values the way that ENS api expects them.
    $.each($('.en__field__input--checkbox:checked'), function (i, field) {
      // If no value is set then use 'Y' as default value, otherwise keep the already set value.
      if ( '' === field.value ) {
        $(this).val('Y');
      }
    });

    $.each($('.en__field__input--checkbox:not(":checked")'), function (i, field) {
      // If no value is set then use 'N' as default value, otherwise keep the already set value.
      if ( '' === field.value ) {
        $(this).val('N');
      }
    });

    $.each($('#p4en_form').serializeArray(), function (i, field) {
      if (field.name.indexOf('supporter.questions.') >= 0) {
        let id = field.name.split('.')[2];
        if ( 'undefined' === typeof supporter.questions['question.' + id] ) {
          supporter.questions['question.' + id] = field.value;
        } else {
          supporter.questions['question.' + id] = supporter.questions['question.' + id] + '~' + field.value;
        }
      } else if (field.name.indexOf('supporter.') >= 0 && '' !== field.value) {
        supporter[field.name.replace('supporter.', '')] = field.value;
      }
    });

    return {
      standardFieldNames: true,
      supporter: supporter
    };
  };

  enform.addChangeListeners = function(form) {
    $(form.elements).each(function () {
      $(this).off('change').on('change', function () {
        enform.validateForm(form);
      });
    });
  };

  enform.validateEmail = function(email) {
    // Reference: https://stackoverflow.com/questions/46155/how-to-validate-an-email-address-in-javascript
    const re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
  };

  enform.validateUrl = function(url) {
    return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);  //eslint-disable-line no-useless-escape
  };

  enform.addErrorMessage = function(element,msg) {
    if ('undefined' === typeof msg) {
      msg = $(element).data('errormessage');
    }
    $(element).addClass('is-invalid');
    const $invalidDiv = $('<div>');
    $invalidDiv.addClass('invalid-feedback');
    $invalidDiv.html(msg);
    $invalidDiv.insertAfter(element);
  };

  enform.removeErrorMessage = function(element) {
    $(element).removeClass('is-invalid');
    const errorDiv = $(element).next();
    if (errorDiv.length && errorDiv.hasClass('invalid-feedback')) {
      $(errorDiv).remove();
    }
  };

  enform.validateForm = function(form) {
    let formIsValid = true;

    $(form.elements).each(function () {
      enform.removeErrorMessage(this);
      const formValue = $(this).val();

      if (
        $(this).attr('required') && !formValue ||
        'email' === $(this).attr('type') && !enform.validateEmail(formValue)
      ) {
        enform.addErrorMessage(this);
        formIsValid = false;
      }

      const regexPattern = $(this).attr('data-validate_regex');
      if (regexPattern) {
        const regex = new RegExp(regexPattern);
        const res = regex.test(formValue);
        if (!res) {
          enform.addErrorMessage(this, $(this).attr('data-validate_regex_msg'));
          formIsValid = false;
        }
      }


      const callbackFunction = $(this).attr('data-validate_callback');
      if ('function' === typeof window[callbackFunction]) {
        const validateField = window[callbackFunction]($(this).val());
        if (true !== validateField) {
          enform.addErrorMessage(this, validateField);
          formIsValid = false;
        }
      }
    });

    return formIsValid;
  };

  // Submit to a en page process api endpoint.
  enform.submitToEn = function(formData, sessionToken) {
    const form = $('#enform');
    const en_page_id = $('input[name=en_page_id]').val();
    const uri = `https://e-activist.com/ens/service/page/${en_page_id}/process`;
    $.ajax({
      url: uri,
      type: 'POST',
      contentType: 'application/json',
      crossDomain: true,
      headers: {
        'ens-auth-token': sessionToken
      },
      data: JSON.stringify(formData),
    }).done(function () {
      // DataLayer push event on successful EN form submission.
      if ( typeof google_tag_value !== 'undefined' && google_tag_value ) {
        let dataLayerPayload = {
          'event' : 'petitionSignup'
        };

        const gGoal = $('#enform_goal').val();
        if ( gGoal ) {
          dataLayerPayload.gGoal = gGoal;
        }

        dataLayer.push(dataLayerPayload);
      }

      const redirectURL = form.data('redirect-url');

      if (enform.validateUrl(redirectURL)) {
        window.location = redirectURL;
      } else {
        $('#enform-content').hide();
        $('.thankyou').show();
      }
      $('.enform-notice').html('');
    }).fail(function (response) {
      $('.enform-notice').html('<span class="enform-error">There was a problem with the submission</span>');
      console.log(response); //eslint-disable-line no-console
    }).always(function () {
      enform.hideENSpinner();
    });
  };


  enform.showENSpinner = function() {
    $('#p4en_form_save_button').attr('disabled', true);
    $('.en-spinner').show();
    $('.enform-notice').html('');

  };

  enform.hideENSpinner = function() {
    $('#p4en_form_save_button').attr('disabled', false);
    $('.en-spinner').hide();
  };

  return enform;

})(jQuery);


$(document).ready(function () {
  'use strict';

  // Submit handler for enform
  $('#p4en_form').submit(function (e) {
    e.preventDefault();

    // Don't bug users with validation before the first submit
    p4_enform_frontend.addChangeListeners(this);

    if (p4_enform_frontend.validateForm(this)) {
      const url = en_vars.ajaxurl;

      p4_enform_frontend.showENSpinner();
      $.ajax({
        url: url,
        type: 'POST',
        data: {
          action: 'get_en_session_token',
          '_wpnonce': $('#_wpnonce', $(this)).val(),
        },
      }).done(function (response) {
        const token = response.token;

        if ('' !== token) {
          const values = p4_enform_frontend.getFormData();
          p4_enform_frontend.submitToEn(values, token);
        } else {
          p4_enform_frontend.hideENSpinner();
          $('.enform-notice').html('There was a problem with the submission');
        }
      }).fail(function (response) {
        p4_enform_frontend.hideENSpinner();
        $('.enform-notice').html('There was a problem with the submission');
        console.log(response); //eslint-disable-line no-console
      });
    }
  });
});
