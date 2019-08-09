import {ENForm} from './ENForm.js';

export class ENFormBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;

    registerBlockType('planet4-gutenberg-engagingnetworks/enform', {
      title: 'EN Form',
      icon: 'feedback',
      category: 'planet4-gutenberg-engagingnetworks',

      // Transform the shortcode into a Gutenberg block
      // this is used when a user clicks "Convert to blocks"
      // on the "Classic Editor" block
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            tag: 'shortcake_enform',
            attributes: {
              title: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.title;
                }
              },
              description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.description;
                }
              },
              necessary_cookies_name: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.necessary_cookies_name;
                }
              },
              necessary_cookies_description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.necessary_cookies_description;
                }
              },
              all_cookies_name: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.all_cookies_name;
                }
              },
              all_cookies_description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.all_cookies_description;
                }
              }
            },
          },
        ]
      },
      attributes: {
        en_page_id: {
          type: 'integer',
        },
        enform_goal: {
          type: 'string',
        },
        en_form_style: {
          type: 'string',
        },
        title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        content_title: {
          type: 'string',
        },
        content_description: {
          type: 'string',
        },
        button_text: {
          type: 'string',
        },
        text_below_button: {
          type: 'string',
        },
        thankyou_title: {
          type: 'string',
        },
        thankyou_subtitle: {
          type: 'string',
        },
        thankyou_donate_message: {
          type: 'string',
        },
        thankyou_take_action_message: {
          type: 'string',
        },
        thankyou_url: {
          type: 'string',
        },
        background: {
          type: 'string',
        },
        en_form_id: {
          type: 'integer',
        },
      },
      edit: ({ isSelected, attributes, setAttributes }) => {
        function onTitleChange(value) {
          setAttributes({title: value});
        }

        function onDescriptionChange(value) {
          setAttributes({description: value});
        }

        function onContentTitleChange(value) {
          setAttributes({content_title: value});
        }

        function onContentDescriptionChange(value) {
          setAttributes({content_description: value});
        }

        function onCTAButtonTextChange(value) {
          setAttributes({button_text: value});
        }

        function onCTATextBelowButtonChange(value) {
          setAttributes({text_below_button: value});
        }

        function onSelectedLayoutChange(value) {
          setAttributes({en_form_style: value});
        }

        return <ENForm
          {...attributes}
          isSelected={isSelected}
          onTitleChange={onTitleChange}
          onDescriptionChange={onDescriptionChange}
          onContentTitleChange={onContentTitleChange}
          onContentDescriptionChange={onContentDescriptionChange}
          onCTAButtonTextChange={onCTAButtonTextChange}
          onCTATextBelowButtonChange={onCTATextBelowButtonChange}
          onSelectedLayoutChange={onSelectedLayoutChange}
        />
      },
      save() {
        return null;
      }
    });
  };
}
