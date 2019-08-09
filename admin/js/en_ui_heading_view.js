/* global _, sui, wp */

const editAttributeHeadingEN = sui.views.editAttributeField.extend({
  tagName: 'span',
  className: 'en-attribute-wrapper',
  events: {
    'change input[type="radio"]': 'inputChanged',
  },

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

  inputChanged: function () {
    let $el;

    if (this.model.get('attr')) {
      $el = this.$el.find('[name="' + this.model.get('attr') + '"]');
    }

    if ('p4en_radio' === this.model.attributes.type) {
      this.setValue($el.filter(':checked').first().val());
    }

    this.triggerCallbacks();
  },

  setValue: function (val) {
    this.model.set('value', val);
  },

  triggerCallbacks: function () {
    const shortcodeName = this.shortcode.attributes.shortcode_tag;
    const attributeName   = this.model.get('attr');
    const hookName        = [shortcodeName, attributeName].join('.');
    const changed         = this.model.changed;
    const collection      = _.flatten(_.values(this.views.parent.views._views));
    const shortcode       = this.shortcode;

    /*
		 * Action run when an attribute value changes on a shortcode
		 *
		 * Called as `{shortcodeName}.{attributeName}`.
		 *
		 * @param changed (object)
		 *           The update, ie. { "changed": "newValue" }
		 * @param viewModels (array)
		 *           The collections of views (editAttributeFields)
		 *                         which make up this shortcode UI form
		 * @param shortcode (object)
		 *           Reference to the shortcode model which this attribute belongs to.
		 */
    wp.shortcake.hooks.doAction(hookName, changed, collection, shortcode);

    const en_form_style = $('input[name=' + attributeName + ']:checked').val();
    if ('full-width' === en_form_style) {
      $('#background').prop('disabled', 'disabled');
    } else if ('full-width-bg' === en_form_style) {
      $('#background').prop('disabled', false);
    }

    this.toggleContentFields();
  }
});

sui.views.editAttributeHeadingEN = editAttributeHeadingEN;
