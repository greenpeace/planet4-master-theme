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
              en_page_id: {
                type: 'integer',
                shortcode: function (attributes) {
                  return attributes.named.en_page_id;
                }
              },
              enform_goal: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.enform_goal;
                }
              },
              en_form_style: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.en_form_style;
                }
              },
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
              content_title: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.content_title;
                }
              },
              content_description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.content_description;
                }
              },
              button_text: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.button_text;
                }
              },
              text_below_button: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.text_below_button;
                }
              },
              thankyou_title: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.thankyou_title;
                }
              },
              thankyou_subtitle: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.thankyou_subtitle;
                }
              },
              thankyou_donate_message: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.thankyou_donate_message;
                }
              },
              thankyou_take_action_message: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.thankyou_take_action_message;
                }
              },
              thankyou_url: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.thankyou_url;
                }
              },
              background: {
                type: 'integer',
                shortcode: function (attributes) {
                  return attributes.named.background;
                }
              },
              en_form_id: {
                type: 'integer',
                shortcode: function (attributes) {
                  return attributes.named.en_form_id;
                }
              },
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
          type: 'integer',
        },
        en_form_id: {
          type: 'integer',
        },
      },
      edit: ({attributes, isSelected, setAttributes}) => {
        function onPageChange(value) {
          setAttributes({en_page_id: parseInt(value)});
        }

        function onGoalChange(value) {
          setAttributes({enform_goal: value});
        }

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

        function onSelectImage(imageData) {
          setAttributes({background: Number(imageData.id)});
        }

        function onSelectURL({url}) {
          setAttributes({id: null});
        }

        function onMainThankYouTextChange(value) {
          setAttributes({thankyou_title: value});
        }

        function onSecondaryThankYouMessageChange(value) {
          setAttributes({thankyou_subtitle: value});
        }

        function onThankYouTakeActionMessageChange(value) {
          setAttributes({thankyou_take_action_message: value});
        }

        function onThankYouDonateMessageChange(value) {
          setAttributes({thankyou_donate_message: value});
        }

        function onThankYouURLChange(value) {
          setAttributes({thankyou_url: value});
        }

        function onFormChange(value) {
          setAttributes({en_form_id: Number(value)});
        }

        function onUploadError({message}) {
          console.log(message);
        }

        return <ENForm
          {...attributes}
          isSelected={isSelected}
          onPageChange={onPageChange}
          onGoalChange={onGoalChange}
          onTitleChange={onTitleChange}
          onDescriptionChange={onDescriptionChange}
          onContentTitleChange={onContentTitleChange}
          onContentDescriptionChange={onContentDescriptionChange}
          onCTAButtonTextChange={onCTAButtonTextChange}
          onCTATextBelowButtonChange={onCTATextBelowButtonChange}
          onSelectedLayoutChange={onSelectedLayoutChange}
          onSelectImage={onSelectImage}
          onSelectURL={onSelectURL}
          onMainThankYouTextChange={onMainThankYouTextChange}
          onSecondaryThankYouMessageChange={onSecondaryThankYouMessageChange}
          onThankYouTakeActionMessageChange={onThankYouTakeActionMessageChange}
          onThankYouURLChange={onThankYouURLChange}
          onThankYouDonateMessageChange={onThankYouDonateMessageChange}
          onFormChange={onFormChange}
          onUploadError={onUploadError}
        />
      },
      save() {
        return null;
      }
    });
  };
}
