/* global p4_enblock, wp, shortcodeUIFieldData */

jQuery(function ($) {
  'use strict';

  if ('undefined' !== typeof (wp.shortcake)) {
    shortcodeUIFieldData.p4en_radio = {
      encode: false,
      template: 'shortcode-ui-field-p4-en-radio',
      view: 'editAttributeHeadingEN'
    };

    const p4_en_blocks = {
      enform: {

        /**
         * Called when a new enform block is rendered in the backend.

         * @param shortcode Shortcake backbone model.
         */
        render_new: function (shortcode) {
          this.toggleContentFields();

          // Get filtered fields.
          const filtered = this.filter_enform_fields(shortcode);

          // Hide all mandatory checkboxes for new enforms.
          $('input[name$="__mandatory"]').parent().parent().hide();

          filtered.forEach(function (element) {
            let attr_name    = element.get('attr');
            let element_name = element.get('name');
            let $element     = $('input[name="' + attr_name + '"]');

            if ( 'emailAddress' === element_name ) {
              $element
                .click()  // Do click instead of setting checked property, because shortcake needs to catch the click event.
                .attr('readonly', 'readonly')
                .attr('onclick', 'return false;');
              $('input[name=' + attr_name + '__mandatory]')
                .click()
                .attr('readonly', 'readonly')
                .attr('onclick',  'return false;')
                .parent().parent().show();
            }
          });

          $('[required]:visible', $('.edit-shortcode-form')).off('change keyup').on('change keyup', function() {
            if ( ! $(this).val() || ( $(this).is('select') && '0' === $(this).val() ) ) {
              $(this).addClass('enform-required-field');
            } else {
              $(this).removeClass('enform-required-field');
            }
          });

          this.add_click_events_for_filtered_fields(filtered);
        },

        /**
         * Called when en existing enform block is rendered in the backend.

         * @param shortcode Shortcake backbone model.
         */
        render_edit: function (shortcode) {
          this.toggleContentFields();

          // Get filtered fields
          const filtered = this.filter_enform_fields(shortcode);

          // Hide all mandatory checkboxes for non selected en fields.
          filtered.forEach(function (element) {
            let attr_name    = element.get('attr');
            let element_name = element.get('name');
            let $element     = $('input[name="' + attr_name + '"]');

            if (!$element.is(':checked')) {
              $('input[name="' + attr_name + '__mandatory"]').parent().parent().hide();
            }

            if ( 'emailAddress' === element_name ) {
              $element
                .attr('readonly', 'readonly')
                .attr('onclick',  'return false;');
              $('input[name=' + attr_name + '__mandatory]')
                .attr('readonly', 'readonly')
                .attr('onclick',  'return false;')
                .parent().parent().show();
            }
          });

          $('[required]:visible', $('.edit-shortcode-form')).off('change keyup').on('change keyup', function() {
            if ( ! $(this).val() || ( $(this).is('select') && '0' === $(this).val() ) ) {
              $(this).addClass('enform-required-field');
            } else {
              $(this).removeClass('enform-required-field');
            }
          });

          this.add_click_events_for_filtered_fields(filtered);
        },

        /**
         * Called when an enform block is destroyed in the backend.

         * @param shortcode Shortcake backbone model.
         */
        render_destroy: function (shortcode) {
          let required_is_empty = false;

          $('[required]:visible', $('.edit-shortcode-form')).each( function() {
            if ( ! $(this).val() || ( $(this).is('select') && '0' === $(this).val() ) ) {
              $(this).addClass('enform-required-field');

              if ( ! required_is_empty ) {
                $(this).focus();
              }
              required_is_empty = true;
            }
          });

          // Here we have to prevent the block from Updating so the only way to do this at this point
          // is to stop JS execution by throwing an error.
          if ( required_is_empty ) {
            throw new Error('Required field is empty!');
          }

          // Prepend Protocol to URL if user has not provided it.
          let models = shortcode.attributes.attrs.models;

          models.forEach(function (model) {
            let attr_name = model.get('attr');

            if ( 'thankyou_url' === attr_name ) {
              let $element  = $('input[name="' + attr_name + '"]');
              let thankyou_url = $element.val();

              if ( '' !== thankyou_url && 0 !== thankyou_url.indexOf('http') ) {
                thankyou_url = 'http://' + thankyou_url;
                model.set('value', thankyou_url);
                $element.val(thankyou_url);
              }
            }
          });

          // Get filtered fields names.
          const filtered = this.filter_enform_fields(shortcode);

          // Remove click events for filtered en fields.
          this.remove_click_events_for_filtered_fields(filtered);
        },

        /**
         * Get en fields and questions checkboxes from an enform block.
         *
         * @param shortcode Shortcake backbone model.
         */
        filter_enform_fields: function (shortcode) {
          return shortcode.attributes.attrs.filter(function (field) {
            return ( field.get('attr').match(/^\d+/) || field.get('attr').match(/^field__+/) ) && !field.get('attr').match(/_mandatory$/);
          });
        },

        get_filtered_names: function (filtered) {
          return filtered.map(function (field) {
            return field.get('attr');
          });
        },

        /**
         * Toggle content fields
         */
        toggleContentFields: function () {
          const en_form_style = $('input[name=en_form_style]:checked').val();
          if ('side-style' === en_form_style) {
            $('.field-block.shortcode-ui-attribute-content_title').show();
            $('.field-block.shortcode-ui-attribute-content_description').show();
          } else {
            $('.field-block.shortcode-ui-attribute-content_title').hide();
            $('.field-block.shortcode-ui-attribute-content_description').hide();
          }
        },

        /**
         * Add click event handlers for all enform filtered fields, to show/hide their mandatory corresponding checkbox.
         *
         * @param filtered
         */
        add_click_events_for_filtered_fields: function (filtered) {

          // Get filtered fields names.
          const fields_names = this.get_filtered_names(filtered);

          // Get jquery objects for each element.
          const element_list = $.map(fields_names, function (el) {
            return $('input[name="' + el + '"]').get();
          });

          // Add click event handlers for the elements.
          $(element_list).on('click', function (event) {
            const element_name = event.currentTarget.name;
            const $element = $(event.currentTarget);

            if ($element.attr('readonly') ) {
              return false;
            }

            if ($element.is(':checked')) {
              $('input[name="' + element_name + '__mandatory"]').parent().parent().show();
            } else {
              $('input[name="' + element_name + '__mandatory"]').prop('checked', false).parent().parent().hide();
            }
          });
        },

        /**
         * Remove click event handlers for all enform filtered fields.
         *
         * @param filtered
         */
        remove_click_events_for_filtered_fields: function (filtered) {

          // Get filtered fields names.
          const filtered_names = this.get_filtered_names(filtered);

          // Remove click events for filtered en fields.
          const filtered_objects = $.map(filtered_names, function (element_name) {
            return $('input[name="' + element_name + '"]').get();
          });
          $(filtered_objects).off('click');
        },

      }
    };

    // Attach hooks when rendering a new en block.
    wp.shortcake.hooks.addAction('shortcode-ui.render_new', function (shortcode) {
      if ('shortcake_' + p4_enblock.block_name === shortcode.get('shortcode_tag')) {
        // Call enform render new function.
        p4_en_blocks.enform.render_new(shortcode);
      }
    });

    // Attach hooks when destroying an en block.
    wp.shortcake.hooks.addAction('shortcode-ui.render_destroy', function (shortcode) {
      if ('shortcake_' + p4_enblock.block_name === shortcode.get('shortcode_tag')) {
        // Call enform render destroy function.
        p4_en_blocks.enform.render_destroy(shortcode);
      }
    });

    // Attach hooks when editing an existing en block.
    wp.shortcake.hooks.addAction('shortcode-ui.render_edit', function (shortcode) {
      if ('shortcake_' + p4_enblock.block_name === shortcode.get('shortcode_tag')) {
        // Call enform render edit function.
        p4_en_blocks.enform.render_edit(shortcode);
      }
    });
  }
});
