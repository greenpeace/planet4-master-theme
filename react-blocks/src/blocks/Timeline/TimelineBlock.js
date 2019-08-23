import {Timeline} from './Timeline.js';

const {__} = wp.i18n;

export class TimelineBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;

    registerBlockType('planet4-blocks/timeline', {
      title: __('Timeline', 'p4ge'),
      icon: 'clock',
      category: 'planet4-blocks',

      // Transform the shortcode into a Gutenberg block
      // this is used when a user clicks "Convert to blocks"
      // on the "Classic Editor" block
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            tag: 'shortcake_timeline',
            attributes: {
              timeline_title: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.timeline_title;
                }
              },
              description: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.description;
                }
              },
              google_sheets_url: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.google_sheets_url;
                }
              },
              language: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.language;
                }
              },
              timenav_position: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.timenav_position;
                }
              },
              start_at_end: {
                type: 'boolean',
                shortcode: function (attributes) {
                  return attributes.named.start_at_end;
                }
              }
            },
          },
        ]
      },
      attributes: {
        timeline_title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        google_sheets_url: {
          type: 'string',
        },
        language: {
          type: 'string',
        },
        timenav_position: {
          type: 'string',
        },
        start_at_end: {
          type: 'boolean',
        },
      },
      edit: ({ isSelected, attributes, setAttributes }) => {
        function onTimelineTitleChange(value) {
          setAttributes({timeline_title: value});
        }

        function onDescriptionChange(value) {
          setAttributes({description: value});
        }

        function onGoogleSheetsUrlChange(value) {
          setAttributes({google_sheets_url: value});
        }

        function onLanguageChange(value) {
          setAttributes({language: value});
        }

        function onTimenavPositionChange(value) {
          setAttributes({timenav_position: value});
        }

        function onStartAtEndChange(value) {
          setAttributes({start_at_end: value});
        }

        return <Timeline
          {...attributes}
          isSelected={isSelected}
          onTimelineTitleChange={onTimelineTitleChange}
          onDescriptionChange={onDescriptionChange}
          onGoogleSheetsUrlChange={onGoogleSheetsUrlChange}
          onLanguageChange={onLanguageChange}
          onTimenavPositionChange={onTimenavPositionChange}
          onStartAtEndChange={onStartAtEndChange}
        />
      },
      save() {
        return null;
      }
    });
  };
}
