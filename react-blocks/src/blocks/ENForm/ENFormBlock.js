import {ENForm} from './ENForm.js';

export class ENFormBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;

    registerBlockType('planet4-engagingnetworks/enform', {
      title: 'EN Form',
      icon: 'visibility',
      category: 'planet4-engagingnetworks',

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
        title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        necessary_cookies_name: {
          type: 'string',
        },
        necessary_cookies_description: {
          type: 'string',
        },
        all_cookies_name: {
          type: 'string',
        },
        all_cookies_description: {
          type: 'string',
        },
      },
      edit: ({ isSelected, attributes, setAttributes }) => {
        function onTitleChange(value) {
          setAttributes({title: value});
        }

        function onDescriptionChange(value) {
          setAttributes({description: value});
        }

        function onNecessaryCookiesNameChange(value) {
          setAttributes({necessary_cookies_name: value});
        }

        function onNecessaryCookiesDescriptionChange(value) {
          setAttributes({necessary_cookies_description: value});
        }

        function onAllCookiesNameChange(value) {
          setAttributes({all_cookies_name: value});
        }

        function onAllCookiesDescriptionChange(value) {
          setAttributes({all_cookies_description: value});
        }

        return <ENForm
          {...attributes}
          isSelected={isSelected}
          onTitleChange={onTitleChange}
          onDescriptionChange={onDescriptionChange}
          onNecessaryCookiesNameChange={onNecessaryCookiesNameChange}
          onNecessaryCookiesDescriptionChange={onNecessaryCookiesDescriptionChange}
          onAllCookiesNameChange={onAllCookiesNameChange}
          onAllCookiesDescriptionChange={onAllCookiesDescriptionChange}
        />
      },
      save() {
        return null;
      }
    });
  };
}
