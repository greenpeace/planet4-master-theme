/* global ajaxurl, Backbone */

const enformTemplates = {
  enSelectedField: data => {
    const __ = wp.i18n.__;
    const escAttr = wp.escapeHtml.escapeAttribute;

    return `
    <tr class="field-item"
        data-en-id="${data.id}"
        data-en-name="${data.name}"
        data-en-type="${data.en_type}">
      <td><a><span class="dashicons dashicons-sort pointer"></span></a></td>
      <td style="width:25%;">${data.name}</td>
      <td>${data.en_type}</td>
      <td>
        <input type="checkbox" data-attribute="required"
               ${data.required ? 'checked' : ''} ${data.input_type === 'hidden' ? 'disabled' : ''} />
      </td>
      <td>
        <input type="text" data-attribute="label" value="${escAttr(data.label)}"
               ${['hidden', 'checkbox', 'radio'].includes(data.input_type) ? 'disabled' : ''} />
      </td>
      <td>
        <select class="field-type-select" data-attribute="input_type">
          <option value="0">${__('--Select--', 'planet4-engagingnetworks-backend')}</option>
          <option value="checkbox" ${data.en_type === 'checkbox' ? 'selected' : ''}>${__('Checkbox', 'planet4-engagingnetworks-backend')}</option>
          <option value="country" ${data.en_type === 'country' ? 'selected' : ''} >${__('Country', 'planet4-engagingnetworks-backend')}</option>
          <option value="position" ${data.en_type === 'position' ? 'selected' : ''} >${__('Position', 'planet4-engagingnetworks-backend')}</option>
          <option value="email" ${data.en_type === 'email' ? 'selected' : ''} >${__('Email', 'planet4-engagingnetworks-backend')}</option>
          <option value="hidden" ${data.en_type === 'hidden' ? 'selected' : ''} >${__('Hidden', 'planet4-engagingnetworks-backend')}</option>
          <option value="text" ${data.en_type === 'text' ? 'selected' : ''} >${__('Text', 'planet4-engagingnetworks-backend')}</option>
          <option value="radio" ${data.en_type === 'radio' ? 'selected' : ''} >${__('Radio', 'planet4-engagingnetworks-backend')}</option>
        </select>
      </td>
      <td class="actions">
        ${data.input_type !== 'email' ? `
        <a><span class="dashicons dashicons-edit pointer"></span></a>
        <a><span class="dashicons dashicons-no remove-en-field"></span></a>
        ` : ''}
      </td>
    </tr>
    `;
  },
  enTextFieldDialog: data => {
    const __ = wp.i18n.__;
    const escAttr = wp.escapeHtml.escapeAttribute;

    return `
    <div style="display: none;" class="dialog">
      <p>${__('Text Field customization', 'planet4-engagingnetworks-backend')}</p>
      <hr>
      <p>${__('The next two fields handle the validation of the text input. If both regex and function name are entered, only the regex will be used.', 'planet4-engagingnetworks-backend')}</p>
      <hr>
      <label class="control-label">${__('JS Function validation regex', 'planet4-engagingnetworks-backend')}</label>
      <div>
        <p>Use the regex <b>without</b> leading and trailing / character</p>
        <p> <a href="https://planet4.greenpeace.org/handbook/block-form/#regex-validation" target="_blank"> Documentation in handbook </a></p>
        <input type="text" class="form-control" data-attribute="js_validate_regex"
             value="${escAttr(data.js_validate_regex || '')}"/>
      </div>
      <label class="control-label">${__('JS validation regex error message', 'planet4-engagingnetworks-backend')}</label>
      <div>
        <input type="text" class="form-control" data-attribute="js_validate_regex_msg"
             value="${escAttr(data.js_validate_regex_msg || '')}"/>
      </div>

      <hr>
      <label class="control-label">${__('JS Function validation callback', 'planet4-engagingnetworks-backend')}</label>
      <div>
        <p><a href="https://planet4.greenpeace.org/handbook/block-form/#callback-js-function" target="_blank">
            Documentation in handbook </a></p>
        <input type="text" class="form-control" data-attribute="js_validate_function"
             value="${escAttr(data.js_validate_function || '')}"/>
      </div>
    </div>
    `;
  },
  enHiddenField: data => {
    const __ = wp.i18n.__;
    const escAttr = wp.escapeHtml.escapeAttribute;

    return `
    <div style="display: none;" class="dialog">
      <p>${__('Hidden Field customization', 'planet4-engagingnetworks-backend')}</p>
      <p>
        <label class="control-label">${__('Default Value', 'planet4-engagingnetworks-backend')}</label>
        <div>
          <input type="text" class="form-control"
                 data-attribute="default_value"
                 value="${escAttr(data.default_value)}" />
        </div>
      </p>
    </div>
    `;
  },
  enCheckboxDialog: data => {
    const __ = wp.i18n.__;
    const escAttr = wp.escapeHtml.escapeAttribute;

    const options = [];
    Object.keys(data.locales || {}).forEach(locale => {
      options.push(`<option value="${escAttr(data.locales[locale])}"
          ${data.locales[locale] === data.lobel_option ? 'selected' : ''}>${locale}</option>`);
    });

    let genOptions = [];
    if (data.en_type === 'GEN') {
      genOptions = data.question_options[data.selected_locale].map(opt => {
        return `<input type="checkbox" value="${escAttr(opt.option_value)}"
            ${opt.option_selected ? 'selected' : ''} disabled />${opt.option_label}<br />`;
      });
    }

    return `
    <div style="display: none;" class="dialog" data-en-id="${data.id}">
      <p>
        <label class="control-label">${__('Locales', 'planet4-engagingnetworks-backend')}}</label>
        <div>
          <select class="form-control question-locale-select" data-attribute="selected_locale">
            ${options.join('')}
          </select>
        </div>
        <br />
        <strong>${data.en_type === 'GEN' ?
    __('Question', 'planet4-engagingnetworks-backend') :
    __('Opt-in', 'planet4-engagingnetworks-backend')}</strong>
        <br /><label class="question-label">${data.label}</label><br /><br />
        ${genOptions.join('')}
      </p>
      <label class="control-label">${__('Dependency', 'planet4-engagingnetworks-backend')}</label>
      <div>
        <select class="form-control dependency-select" id="dependency-${data.id}"
                data-attribute="dependency" name="dependency">
          <option value="">${__('Select Dependency', 'planet4-engagingnetworks-backend')}</option>
        </select><span></span>
      </div>
    </div>
    `;
  },
  enRadioDialog: data => {
    const __ = wp.i18n.__;
    const escAttr = wp.escapeHtml.escapeAttribute;

    const options = [];
    Object.keys(data.locales).forEach(locale => {
      options.push(`<option value="${escAttr(data.locales[locale])}"
          ${data.locales[locale] === data.lobel_option ? 'selected' : ''}>${locale}</option>`);
    });

    let radioOptions = [];
    if (data.en_type === 'GEN') {
      radioOptions = data.radio_options[data.selected_locale].map(opt => {
        return `<input type="radio"
            id="en__field_supporter_${opt.name}"
            class="en__field__input en__field__input--radio"
            value="${escAttr(opt.option_value)}"
            ${opt.option_selected ? 'checked' : ''} disabled />${opt.option_label}<br />`;
      });
    }
    return `
    <div style="display: none;" class="dialog" data-en-id="${escAttr(data.id)}">
      <p>
        <label class="control-label">${__('Locales', 'planet4-engagingnetworks-backend')}</label>
        <div>
          <select class="form-control question-locale-select" data-attribute="selected_locale">
            ${options.join('')}
          </select>
        </div>
        <br />
        <strong>${data.en_type === 'GEN' ?
    __('Question', 'planet4-engagingnetworks-backend') :
    __('Opt-in', 'planet4-engagingnetworks-backend')}</strong>
        <br /><label class="question-label">${data.label}</label><br /><br />
        ${radioOptions.join('')}
      </p>
    </div>
    `;
  },

};

/**
 * Define models, collections, views for P4 ENForms.
 */
const p4_enform = ($ => {
  const app = {
    Models: {},
    Collections: {},
    Views: {},
  };

  /**
   * Model for ENForm field.
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
      dependency: '',
    },
  });

  /**
   * Collection of fields.
   */
  app.Collections.EnformFields = Backbone.Collection.extend(
    {
      model: app.Models.EnformField,
      url: '',
    });

  /**
   * A view for listing fields.
   */
  app.Views.FieldsListView = Backbone.View.extend({
    el: '#en_form_selected_fields_table',
    template: () => '',
    events: {
      'click .remove-en-field': 'removeField',
      'update-sort': 'updateSort',
    },
    views: {},

    /**
     * Initialize view.
     */
    initialize() {
      this.listenTo(this.collection, 'add', this.renderOne);
    },

    /**
     * Render a single field.
     *
     * @param {Object} field      Field model.
     * @param {Object} collection Field model collection.
     * @param {Object} actions    Object with actions.
     */
    renderOne(field, collection, actions) {
      const fieldView = new app.Views.FieldsListItemView({model: field});

      this.views[field.id] = fieldView;
      $('#en_form_selected_fields_table > tbody').append(fieldView.render());
      $('.add-en-field').filter('*[data-id="' + field.id + '"]').prop('disabled', true);
      fieldView._delegateEvents();

      // If a field is being added and its html type has been retrieved from EN
      // then auto-select the field type for Questions/Optins. Should happen after Delegate events.
      if (actions.add && field.attributes.htmlFieldType) {
        if ('OPT' === field.attributes.en_type || 'GEN' === field.attributes.en_type) {
          $('.field-type-select', fieldView.$el).val(field.attributes.htmlFieldType).change();
        }
      }
      fieldView.createFieldDialog();
    },

    /**
     * Render view.
     */
    render() {
      this.collection.models.forEach(project => {
        this.renderOne(project, this.collection, {add: false});
      }, this);
      this.disableEmailField();
    },

    /**
     * Event listener for remove field/question button.
     *
     * @param {Object} e
     */
    removeField(e) {
      e.preventDefault();
      const $tr = $(e.target).closest('tr');
      const id = $tr.data('en-id');

      $('.add-en-field').filter('*[data-id="' + id + '"]').prop('disabled', false);
      this.collection.remove(this.collection.findWhere({id}));
      this.views[id].destroy();
      $tr.remove();
    },

    /**
     * Reorder collection models.
     *
     * @param {Object} event    Event object
     * @param {Object} model    Field Model.
     * @param {number} position New index.
     */
    updateSort(event, model, position) {
      this.collection.remove(model, {silent: true});
      this.collection.add(model, {at: position, silent: true});
    },

    /**
     * Disable email field attributes besides label.
     */
    disableEmailField() {
      $('tr[data-en-name="Email"] span.remove-en-field').remove();
      $('tr[data-en-name="Email"] input[data-attribute="required"]').prop('checked', true).prop('disabled', true);
      $('tr[data-en-name="Email"] select[data-attribute="input_type"]').val('email').prop('disabled', true);
      const emailModel = this.collection.findWhere({property: 'emailAddress'});
      if ('undefined' !== typeof emailModel) {
        emailModel
          .set('input_type', 'email')
          .set('required', true);
      }
    },
  });

  /**
   * A single field view.
   */
  app.Views.FieldsListItemView = Backbone.View.extend({
    className: 'field-item',
    template: enformTemplates.enSelectedField,
    dialog_view: null,

    events: {
      'keyup input[type="text"]': 'inputChanged',
      'change input[type="text"]': 'inputChanged',
      'change input[type="checkbox"]': 'checkboxChanged',
      'change select.field-type-select': 'selectChanged',
      'sort-field': 'sortField',
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    inputChanged(event) {
      const $target = $(event.target);
      const value = $target.val();
      const attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    checkboxChanged(event) {
      const $target = $(event.target);
      const value = $target.is(':checked');
      const attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Register event listener for field type select box.
     *
     * @param {Object} event
     */
    selectChanged(event) {
      const input_type = $(event.target).val();
      const $tr = $(event.target).closest('tr');
      const id = $tr.data('en-id');
      const attr = $(event.target).data('attribute');
      const en_type = this.model.get('en_type');
      const $label = this.$el.find('input[data-attribute="label"]');
      const $required = this.$el.find('input[data-attribute="required"]');

      this.model.set(attr, input_type);
      $tr.find('.dashicons-edit').parent().remove();

      switch (input_type) {
      case 'checkbox':
        if ('OPT' === en_type || 'GEN' === en_type) {
          $required.prop('disabled', false);
          $label.prop('disabled', true);
        } else {
          $label.prop('disabled', false);
        }
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      case 'country':
        $required.prop('disabled', false);
        $label.prop('disabled', false);
        break;

      case 'position':
        $required.prop('disabled', false);
        $label.prop('disabled', false);
        break;

      case 'email':
        $required.prop('disabled', false);
        $label.prop('disabled', false);
        break;

      case 'hidden':
        $required.prop('checked', false).trigger('change').prop('disabled', true);
        $label.prop('disabled', true);
        $label.val('').trigger('change');
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      case 'text':
        $required.prop('disabled', false);
        $label.prop('disabled', false);
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      case 'radio':
        $required.prop('disabled', false);
        if ('OPT' === en_type || 'GEN' === en_type) {
          $label.prop('disabled', true);
        } else {
          $label.prop('disabled', false);
        }
        this.$el.find('.actions').prepend('<a><span class="dashicons dashicons-edit pointer"></span></a>');
        this.createFieldDialog();
        break;

      default:
        if (null !== this.dialog_view) {
          this.dialog_view.destroy();
          this.dialog_view = null;
        }
        $('body').find('.dialog-' + id).remove();
        this.$el.find('.dashicons-edit').parent().remove();
      }
    },

    /**
     * Initialize view.
     */
    initialize() {
      this.listenTo(this.model, 'change', this.render);
    },

    /**
     * Create field dialog view.
     */
    createFieldDialog() {
      const input_type = this.model.get('input_type');
      let tmpl = '';

      switch (input_type) {
      case 'text':
        tmpl = enformTemplates.enTextFieldDialog;
        break;
      case 'hidden':
        tmpl = enformTemplates.enHiddenField;
        break;
      case 'checkbox':
        tmpl = enformTemplates.enCheckboxDialog;
        break;
      case 'radio':
        tmpl = enformTemplates.enRadioDialog;
        break;
      }

      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
        $('body').find('.dialog-' + this.model.id).remove();
      }

      if (tmpl) {
        this.dialog_view = new app.Views.FieldDialog({
          row: this.model.id,
          model: this.model,
          template: tmpl,
        });
      }
    },

    /**
     * Delegate events after view is rendered.
     */
    _delegateEvents() {
      this.$el = $('tr[data-en-id="' + this.model.id + '"]');
      this.delegateEvents();
    },

    /**
     * Render view.
     *
     * @return {Object} the template.
     */
    render() {
      return this.template(this.model.toJSON());
    },

    /**
     * Destroy view.
     */
    destroy() {
      if (null !== this.dialog_view) {
        this.dialog_view.destroy();
      }
      this.remove();
    },

    /**
     * Trigger collection sorting.
     *
     * @param {Object} event Event object
     * @param {number} index New index for the field model.
     */
    sortField(event, index) {
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
      'change .dependency-select': 'dependencyChanged',
    },

    /**
     * Handles input text value changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    inputChanged(event) {
      const $target = $(event.target);
      const value = $target.val();
      const attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles input checkbox value changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    checkboxChanged(event) {
      const $target = $(event.target);
      const value = $target.is(':checked');
      const attr = $target.data('attribute');
      this.model.set(attr, value);
    },

    /**
     * Handles locale select changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    localeChanged(event) {
      const $target = $(event.target);
      const $dialog = $target.closest('div.dialog');
      const field_id = $dialog.attr('data-en-id');
      const label = $(event.target).val();
      const locale = $('option:selected', $target).text();

      $('input[data-attribute="label"]', $('tr[data-en-id="' + field_id + '"]'))
        .prop('disabled', false)
        .val(label)
        .trigger('change')
        .prop('disabled', true);

      this.model.set('label', label);
      this.model.set('selected_locale', locale);

      // Get template's html, unwrap it to get rid of the most outer element and then update the dialog's html with it.
      const dialog_html = $(this.template(this.model.toJSON())).unwrap().html();
      $dialog.html(dialog_html);
    },

    /**
     * Handles dependency select changes and stores them to the model.
     *
     * @param {Object} event Event object.
     */
    dependencyChanged(event) {
      const $target = $(event.target);
      const value = $('option:selected', $target).val();
      $('option:selected', $target).attr('selected', 'selected');
      this.model.set('dependency', value);
    },

    /**
     * Initialize view instance.
     *
     * @param {Object} options Options object.
     */
    initialize(options) {
      this.template = options.template;
      this.rowid = options.row;
      this.row = $('tr[data-en-id="' + this.rowid + '"]');
      this.model = options.model;
      this.render();
    },

    /**
     * Render dialog view
     */
    render() {
      $(this.row).find('.actions').prepend(this.template(this.model.toJSON()));

      this.dialog = $(this.row).find('.dialog').dialog({
        autoOpen: false,
        height: 450,
        width: 350,
        modal: true,
        title: 'Edit: ' + this.model.get('name'),
        dialogClass: 'dialog-' + this.rowid,
        buttons: {
          'Close'() {
            dialog.dialog('close');
          },
        },
      });

      this.el = '.dialog-' + this.rowid;
      this.$el = $(this.el).find('.ui-dialog-content');
      const label = $('.question-locale-select', this.$el).val();
      this.delegateEvents();

      const dialog = this.dialog;
      $(this.row).find('.dashicons-edit').off('click').on('click', e => {
        e.preventDefault();
        const button = e.target;

        // Filter dependency fields and add them on dialog popup.
        let dependency_options = '';
        if ('checkbox' === $(button).closest('tr').find('.field-type-select').val()) {
          const selected_en_fields = p4_enform.fields.models;

          if (selected_en_fields.length) {
            const dependency_array = [];
            let dependency_field = '';
            const field_name = $(button).closest('tr').find('td:eq(1)').text();
            selected_en_fields.forEach(field => {
              if ('checkbox' === field.attributes.input_type && field.attributes.name !== field_name) {
                dependency_array.push(field.attributes.name);
              }

              if (field.attributes.name === field_name) {
                dependency_field = field.attributes.dependency;
              }
            });

            $.each(dependency_array, (key, value) => {
              let selected_option = '';
              if (dependency_field === value) {
                selected_option = 'selected';
              }
              dependency_options += '<option value="' + value + '" ' + selected_option + '>' + value + '</option>';
            });
          }
        }

        dialog.html(dialog.html().replace('</select><span></span>', dependency_options + '</select>'));
        dialog.dialog('open');
      });

      // Handle Label selection.
      $('.question-label', this.$el).html(label);
      $('.question-locale-select').change();
    },

    /**
     * Destroy dialog view.
     * Set default values to model.
     */
    destroy() {
      this.dialog.dialog('destroy');
      this.model.set('default_value', '');
      this.model.set('js_validate_regex', '');
      this.model.set('js_validate_regex_msg', '');
      this.model.set('js_validate_function', '');
      this.model.set('hidden', false);
      this.model.set('dependency', '');
      this.remove();
    },
  });

  return app;
})(jQuery);

jQuery($ => {
  /**
   * Event listener for add field/question button.
   */
  $('.add-en-field').off('click').on('click', e => {
    e.preventDefault();
    const escAttr = wp.escapeHtml.escapeAttribute;
    const escHTML = wp.escapeHtml.escapeHTML;

    const button = e.target;
    $(button).prop('disabled', true);
    const field_data = {
      name: $(button).data('name'),
      en_type: $(button).data('type'),
      property: $(button).data('property'),
      id: $(button).data('id'),
      htmlFieldType: '',
      selected_locale: '',
      locales: {},
      question_options: {},
      radio_options: {},
      selected: '',
    };

    // If we add an Opt-in then retrieve the labels for all locales that exist for it from EN.
    if ('OPT' === field_data.en_type || 'GEN' === field_data.en_type) {
      $.ajax({
        url: ajaxurl,
        type: 'GET',
        data: {
          action: 'get_supporter_question_by_id',
          id: $(button).data('id'),
        },
      }).done(response => {
        // Checking response type to avoid error if string was returned (in case of an error).
        if ('object' === typeof response) {
          $.each(response, (i, value) => {
            if (value.content && 'undefined' !== typeof value.content.data[0]) {
              field_data.htmlFieldType = value.htmlFieldType;
              let label = '';
              let selected = '';

              switch (field_data.htmlFieldType) {
              case 'checkbox':
                if ('OPT' === field_data.en_type) {
                  label = value.content.data[0].label;
                  selected = value.content.data[0].selected;
                } else if ('GEN' === field_data.en_type) {
                  label = value.label;
                  field_data.question_options[value.locale] = [];

                  $.each(value.content.data, (j, option) => {
                    field_data.question_options[value.locale].push({
                      option_label: escHTML(option.label),
                      option_value: escAttr(option.value),
                      option_selected: option.selected,
                    });
                  });
                }
                field_data.locales[value.locale] = escHTML(label);
                field_data.selected = selected;
                break;

              case 'radio':
                label = value.label;
                field_data.locales[value.locale] = escHTML(label);
                field_data.radio_options[value.locale] = [];

                $.each(value.content.data, (j, option) => {
                  field_data.radio_options[value.locale].push({
                    option_label: escHTML(option.label),
                    option_value: escAttr(option.value),
                    option_selected: option.selected,
                  });
                });
                break;
              }
            }
          });
          // Add new field.
          p4_enform.fields.add(new p4_enform.Models.EnformField(field_data));
        }
      }).fail(response => {
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
    stop(event, ui) {
      ui.item.trigger('sort-field', ui.item.index());
    },
  });

  /**
   * Hook into post submit to inject form fields.
   */
  $('#post').on('submit', () => {
    $('#p4enform_fields').val(JSON.stringify(p4_enform.fields.toJSON()));
  });

  /**
   * Disable preview form fields.
   */
  $('#meta-box-form :input').prop('disabled', true);
});

// Handles initial page load of new/edit enform page.
// Create fields collections and views and populate views if there are any saved fields.
(($, app) => {
  /**
   * Initialize new/edit enform page.
   */
  app.init_new_enform_page = () => {
    // Create fields collection.
    app.fields = new app.Collections.EnformFields();

    // Instantiate fields collection.
    let fields = $('#p4enform_fields').val();

    // If fields are set populate the fields collection.
    if ('' !== fields) {
      fields = JSON.parse(fields);
      if (fields) {
        const fields_arr = [];
        fields.forEach(field => {
          fields_arr.push(new app.Models.EnformField(field));
        });
        app.fields.add(fields_arr);
      }
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
  $(document).ready(() => {
    // Initialize app when document is loaded.
    app.init_new_enform_page();

    // Initialize tooltips.
    app.fields_view.$el.tooltip({
      track: true,
      show: {effect: 'fadeIn', duration: 500},
    });
  });
})(jQuery, p4_enform);
