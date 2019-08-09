/* global ajaxurl, jQuery, Backbone, _ */

jQuery(function ($) {

  /**
   * Event listener for add field/question button.
   */
  $('.add-en-field').off('click').on('click', function (e) {
    e.preventDefault();

    $(this).prop('disabled', true);
    const field_data = {
      name: $(this).data('name'),
      en_type: $(this).data('type'),
      property: $(this).data('property'),
      id: $(this).data('id'),
      htmlFieldType: '',
      selected_locale: '',
      locales: {},
      question_options: {},
      radio_options: {},
      selected: '',
    };

    // If we add an Opt-in then retrieve the labels for all locales that exist for it from EN.
    if( 'OPT' === field_data.en_type || 'GEN' === field_data.en_type ) {
      $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: {
          action: 'get_supporter_question_by_id',
          id: $(this).data('id')
        },
      }).done(function (response) {

        // Checking response type to avoid error if string was returned (in case of an error).
        if ( 'object' === typeof response ) {
          $.each(response, function (i, value) {
            if (value.content && 'undefined' !== typeof value.content.data[0]) {
              field_data['htmlFieldType'] = value.htmlFieldType;
              let label = '';
              let selected = '';

              switch ( field_data['htmlFieldType'] ) {
              case 'checkbox':
                if ('OPT' === field_data['en_type']) {
                  label = value.content.data[0].label;
                  selected = value.content.data[0].selected;

                } else if ('GEN' === field_data['en_type']) {
                  label = value.label;
                  field_data['question_options'][value.locale] = [];

                  $.each(value.content.data, function (i, option) {
                    field_data['question_options'][value.locale].push({
                      'option_label': _.escape( option.label ),
                      'option_value': _.escape( option.value ),
                      'option_selected': option.selected
                    });

                  });
                }
                field_data['locales'][value.locale] = _.escape(label);
                field_data['selected'] = selected;
                break;

              case 'radio':
                label = value.label;
                field_data['locales'][value.locale] = _.escape(label);
                field_data['radio_options'][value.locale] = [];

                $.each(value.content.data, function (i, option) {
                  field_data['radio_options'][value.locale].push({
                    'option_label': _.escape( option.label ),
                    'option_value': _.escape( option.value ),
                    'option_selected': option.selected
                  });
                });
                break;
              }
            }
          });
          // Add new field.
          p4_enform.fields.add( new p4_enform.Models.EnformField(field_data) );
        }
      }).fail(function (response) {
        console.log(response); //eslint-disable-line no-console
      });
    } else {
      p4_enform.fields.add(new p4_enform.Models.EnformField(field_data));
    }
  });

  /**
   * Make form selected fields table sortable.
   */
  $('#en_form_selected_fields_table > tbody').sortable({
    handle: '.dashicons-sort',
    stop: function (event, ui) {
      ui.item.trigger('sort-field', ui.item.index());
    }
  });


  /**
   * Hook into post submit to inject form fields.
   */
  $('#post').on('submit', function () {
    $('#p4enform_fields').val(JSON.stringify(p4_enform.fields.toJSON()));
  });

  /**
   * Disable preview form fields.
   */
  $('#meta-box-form :input').prop('disabled', true);

});

/**
 * Define models, collections, views for p4 en forms.
 */
const p4_enform = (function ($) {

  const app = {
    Models: {},
    Collections: {},
    Views: {},
  };

  /**
   * Model for en form field.
   */
  app.Models.EnformField = Backbone.Model.extend({
    urlRoot: '',
    defaults: {
      id: 0,
      name: null,
      property: '',
      label: '',
      default_value: '',
      js_validate_regex: '',
      js_validate_regex_msg: '',
      js_validate_function: '',
      en_type: 'N',
      hidden: false,
      required: false,
      input_type: '0',
      htmlFieldType: '',
      selected_locale: '',
      locales: {},
      question_options: {},
      radio_options: {},
      selected: '',
    }
  });

  /**
   * Collection of fields.
   */
  app.Collections.EnformFields = Backbone.Collection.extend(
    {
      model: app.Models.EnformField,
      url: ''
    });

  /**
   * A view for listing fields.
   */
  app.Views.FieldsListView = Backbone.View.extend({
    el: '#en_form_selected_fields_table',
    template: _.template($('#tmpl-en-selected-fields').html()),
    events: {
      'click .remove-en-field': 'removeField',
      'update-sort': 'updateSort',
    },
    views: {},

    /**
     * Initialize view.
     */
    initialize: function () {
      this.listenTo(this.collection, 'add', this.renderOne);
    },

    /**
     * Render a single field.
     *
     * @param field Field model.
     * @param collection Field model collection.
     * @param actions Object with actions.
     */
    renderOne: function (field, collection, actions ) {
      const fieldView = new app.Views.FieldsListItemView({model: field});

      this.views[field.id] = fieldView;
      $('#en_form_selected_fields_table > tbody').append(fieldView.render());
      $('.add-en-field').filter('*[data-id="' + field.id + '"]').prop('disabled', true);
      fieldView._delegateEvents();

      // If a field is being added and its html type has been retrieved from EN
      // then auto-select the field type for Questions/Optins. Should happen after Delegate events.
      if ( actions.add && field.attributes.htmlFieldType ) {
        if( 'OPT' === field.attributes.en_type || 'GEN' === field.attributes.en_type ) {
          $('.field-type-select', fieldView.$el).val( field.attributes.htmlFieldType ).change();
        }
      }
      fieldView.createFieldDialog();
    },

    /**
     * Render view.
     */
    render: function () {
      _.each(this.collection.models, function (project) {
        this.renderOne(project, this.collection, {'add': false});
      }, this);
      this.disableEmailField();
    },

    /**
     * Event listener for remove field/question button.
     */
    removeField: function (e) {
      e.preventDefault();
      const $tr = $(e.target).closest('tr');
      const id  = $tr.data('en-id');

      $('.add-en-field').filter('*[data-id="' + id + '"]').prop('disabled', false);
      this.collection.remove(this.collection.findWhere({id: id}));
      this.views[id].destroy();
      $tr.remove();
    },

    /**
     * Reorder collection models.
     *
     * @param event Event object
     * @param model Field Model.
     * @param position New index.
     */
    updateSort: function (event, model, position) {
      this.collection.remove(model, {silent: true});
      this.collection.add(model, {at: position, silent: true});
    },

    /**
     * Disable email field attributes besides label.
     */
    disableEmailField: function () {
      $('tr[data-en-name="Email"] span.remove-en-field').remove();
      $('tr[data-en-name="Email"] input[data-attribute="required"]').prop('checked', true).prop('disabled', true);
      $('tr[data-en-name="Email"] select[data-attribute="input_type"]').val('email').prop('disabled', true);
      let emailModel = this.collection.findWhere({property: 'emailAddress'});
      if ('undefined' !== typeof emailModel) {
        emailModel
          .set('input_type', 'email')
          .set('required', true);
      }
    }
  });

  /**
   * A single field view.
   */
  app.Views.FieldsListItemView = Backbone.View.extend({
    className: 'field-item',
    template: _.template($('#tmpl-en-selected-field').html()),
    dialog_view: null,

    events: {
      'keyup input[type="text"]': 'inputChanged',
      'change input[type="text"]': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
      'change select.field-type-select': 'selectChanged',
      'sort-field': 'sortField'
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param event Event object.
     */
    inputChanged(event) {
      const $target = $(event.target);
      const value   = $target.val();
      const attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param event Event object.
     */
    checkboxChanged(event) {
      const $target = $(event.target);
      const value   = $target.is(':checked');
      const attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Register event listener for field type select box.
     */
    selectChanged(event) {
      const input_type = $(event.target).val();
      const $tr        = $(event.target).closest('tr');
      const id         = $tr.data('en-id');
      const attr       = $(event.target).data('attribute');
      const en_type    = this.model.get('en_type');
      let $label       = this.$el.find('input[data-attribute="label"]');

      this.model.set(attr, input_type);
      $tr.find('.dashicons-edit').parent().remove();
      $label.val('').trigger('change');

      switch ( input_type ) {
      case 'text':
        $label.prop('disabled', false);
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      case 'hidden':
        this.$el.find('input[data-attribute="required"]').prop('checked', false).trigger('change').prop('disabled', true);
        $label.prop('disabled', true);
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      case 'checkbox':
        if ( ('OPT' === en_type || 'GEN' === en_type) ) {
          $label.prop('disabled', true);
          this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
          this.createFieldDialog();
        }
        break;

      case 'radio':
        if ( ('OPT' === en_type || 'GEN' === en_type) ) {
          $label.prop('disabled', true);
          this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
          this.createFieldDialog();
        }
        break;

      default:
        if (null !== this.dialog_view) {
          this.dialog_view.destroy();
          this.dialog_view = null;
        }
        $('body').find('.dialog-' + id).remove();
        this.$el.find('.dashicons-edit').parent().remove();
      }

      if ('hidden' !== input_type) {
        this.$el.find('input[data-attribute="required"]').prop('disabled', false);
        if ('OPT' !== en_type) {
          this.$el.find('input[data-attribute="label"]').prop('disabled', false);
        }
      }
    },

    /**
     * Initialize view.
     */
    initialize: function () {
      this.listenTo(this.model, 'change', this.render);
    },

    /**
     * Create field dialog view.
     */
    createFieldDialog: function () {
      const input_type = this.model.get('input_type');
      let tmpl = '';

      switch ( input_type ) {
      case 'text':
        tmpl = '#tmpl-en-text-field-dialog';
        break;
      case 'hidden':
        tmpl = '#tmpl-en-hidden-field-dialog';
        break;
      case 'checkbox':
        tmpl = '#tmpl-en-checkbox-dialog';
        break;
      case 'radio':
        tmpl = '#tmpl-en-radio-dialog';
        break;
      }

      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
        $('body').find('.dialog-' + this.model.id).remove();
      }

      if ( tmpl ) {
        this.dialog_view = new app.Views.FieldDialog({row: this.model.id, model: this.model, template: tmpl});
      }
    },

    /**
     * Delegate events after view is rendered.
     */
    _delegateEvents: function () {
      this.$el = $('tr[data-en-id="' + this.model.id + '"]');
      this.delegateEvents();
    },

    /**
     * Render view.
     */
    render: function () {
      return this.template(this.model.toJSON());
    },

    /**
     * Destroy view.
     */
    destroy: function () {
      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
      }
      this.remove();
    },

    /**
     * Trigger collection sorting.
     *
     * @param event Event object
     * @param index New index for the field model.
     */
    sortField: function (event, index) {
      this.$el.trigger('update-sort', [this.model, index]);
    },
  });

  /**
   * A single field view.
   */
  app.Views.FieldDialog = Backbone.View.extend({
    row: null,
    dialog: null,
    events: {
      'keyup input': 'inputChanged',
      'change input[type="text"]': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
      'change .question-locale-select': 'localeChanged',
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param event Event object.
     */
    inputChanged(event) {
      const $target = $(event.target);
      const value   = $target.val();
      const attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param event Event object.
     */
    checkboxChanged(event) {
      const $target = $(event.target);
      const value   = $target.is(':checked');
      const attr    = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles locale select changes and stores them to the model.
     *
     * @param event Event object.
     */
    localeChanged(event) {
      const $target  = $(event.target);
      const $dialog  = $target.closest('div.dialog');
      const field_id = $dialog.attr('data-en-id');
      const label    = $(event.target).val();
      const locale   = $('option:selected', $target).text();

      $('input[data-attribute="label"]', $('tr[data-en-id="' + field_id + '"]'))
        .prop('disabled', false)
        .val( label )
        .trigger('change')
        .prop('disabled', true);

      this.model.set('label', label);
      this.model.set('selected_locale', locale);

      // Get template's html, unwrap it to get rid of the most outer element and then update the dialog's html with it.
      let dialog_html = $(this.template(this.model.toJSON())).unwrap().html();
      $dialog.html( dialog_html );
    },

    /**
     * Initialize view instance.
     *
     * @param options Options object.
     */
    initialize: function (options) {
      this.template = _.template($(options.template).html());
      this.rowid    = options.row;
      this.row      = $('tr[data-en-id="' + this.rowid + '"]');
      this.model    = options.model;
      this.render();
    },

    /**
     * Render dialog view
     */
    render: function () {
      $(this.row).find('.actions').prepend(this.template(this.model.toJSON()));

      this.dialog = $(this.row).find('.dialog').dialog({
        autoOpen: false,
        height: 450,
        width: 350,
        modal: true,
        title: 'Edit: ' + this.model.get('name'),
        dialogClass: 'dialog-' + this.rowid,
        buttons: {
          'Close': function () {
            dialog.dialog('close');
          }
        },
      });

      this.el   = '.dialog-' + this.rowid;
      this.$el  = $(this.el).find('.ui-dialog-content');
      let label = $('.question-locale-select', this.$el).val();
      this.delegateEvents();

      const dialog = this.dialog;
      $(this.row).find('.dashicons-edit').off('click').on('click', function (e) {
        e.preventDefault();
        dialog.dialog('open');
      });

      // Handle Label selection.
      $('.question-label', this.$el).html( label );
      $('.question-locale-select').change();
    },

    /**
     * Destroy dialog view.
     * Set default values to model.
     */
    destroy: function () {
      this.dialog.dialog('destroy');
      this.model.set('default_value', '');
      this.model.set('js_validate_regex', '');
      this.model.set('js_validate_regex_msg', '');
      this.model.set('js_validate_function', '');
      this.model.set('hidden', false);
      this.remove();
    }
  });

  return app;

})(jQuery);

// Handles initial page load of new/edit enform page.
// Create fields collections and views and populate views if there are any saved fields.
(function ($, app) {

  /**
   * Initialize new/edit enform page.
   */
  app.init_new_enform_page = function () {

    // Create fields collection.
    app.fields = new app.Collections.EnformFields();

    // Instantiate fields collection.
    let fields = $('#p4enform_fields').val();

    // If fields are set populate the fields collection.
    if ('' !== fields) {
      fields = JSON.parse(fields);
      const fields_arr = [];
      _.each(fields, function (field) {
        fields_arr.push(new app.Models.EnformField(field));
      }, this);
      app.fields.add(fields_arr);
    }

    // If it is a new post, add email field.
    if ('auto-draft' === $('#original_post_status').val()) {
      $('button[class="add-en-field"][data-property="emailAddress"] ').click();
    }

    app.fields_view = new app.Views.FieldsListView({collection: app.fields});
    app.fields_view.render();
  };

  /**
   * Initialize app when page is loaded.
   */
  $(document).ready(function () {

    // Initialize app when document is loaded.
    app.init_new_enform_page();

    // Initialize tooltips.
    app.fields_view.$el.tooltip({
      track: true,
      show: { effect: 'fadeIn', duration: 500 }
    });
  });

})(jQuery, p4_enform);
