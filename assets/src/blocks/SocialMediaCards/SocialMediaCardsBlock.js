import { SocialMediaCards } from './SocialMediaCards';

export class SocialMediaCardsBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { withSelect } = wp.data;

    registerBlockType('planet4-blocks/social-media-cards', {
      title: __('Social Media Cards', 'p4ge'),
      icon: 'format-image',
      category: 'planet4-blocks',

      /**
       * Transforms old 'shortcake' shortcode to new gutenberg block.
       */
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            tag: 'shortcake_socail_share',
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
              id: {
                type: 'integer',
                shortcode: ({ named: { id = '' } }) => id,
              },
              multiple_image: {
                type: 'string',
                shortcode: ({ named: { multiple_image = '' } }) => multiple_image,
              },
              gallery_block_focus_points: {
                type: 'string',
                shortcode: ({ named: { gallery_block_focus_points = '' } }) => gallery_block_focus_points,
              },
              messages: {
                type: 'string',
                shortcode: ({ named: { messages = '' } }) => messages,
              },
              urls: {
                type: 'string',
                shortcode: ({ named: { urls = '' } }) => urls,
              },
            },
          },
        ]
      },
      attributes: {
        title: {
          type: 'string',
          default: '',
        },
        description: {
          type: 'string',
          default: '',
        },
        id: {
          type: 'number',
        },

        multiple_image: {
          type: 'string',
        },
        image_data: {
          type: 'object',
          default: []
        },
        gallery_block_focus_points: {
          type: 'string',
        },
        messages: {
          type: 'string',
        },
        urls: {
          type: 'string',
        },
      },
      edit: withSelect((select, props) => {

        const { attributes } = props;
        const { multiple_image } = attributes;

        let image_urls_array = [];

        if (multiple_image) {
          let image_id_array = multiple_image.split(',');

          $.each(image_id_array, function (index, img_id) {
            let img_url = select('core').getMedia(img_id);
            if (img_url) {
              image_urls_array[img_id] = img_url.media_details.sizes.medium.source_url;
            }
          });
        }

        return {
          image_urls_array
        };
      })(({
        image_urls_array,
        isSelected,
        attributes,
        setAttributes
      }) => {

        let { image_data, gallery_block_focus_points, messages, urls } = attributes;

        // Prepare image_data array on edit gallery block.
        if (0 == image_data.length && 0 < image_urls_array.length) {
          let new_image_data = [];
          let focal_points_json = gallery_block_focus_points ? JSON.parse(gallery_block_focus_points) : {};
          let messages_json = messages ? JSON.parse(messages) : {};
          debugger;
          let urlss_json = urls ? JSON.parse(urls) : {};
          for (const img_id in image_urls_array) {

            let x, y;
            if ($.isEmptyObject(focal_points_json)) {
              [x, y] = [50, 50];
            } else {
              [x, y] = focal_points_json[img_id].replace(/\%/g, '').split(' ');
            }

            let mes;

            if ($.isEmptyObject(messages_json)) {
              mes = '';
            } else {
              mes = messages_json[img_id];
            }

            let url;

            if ($.isEmptyObject(urlss_json)) {
              url = '';
            } else {
              url = urlss_json[img_id];
            }

            new_image_data.push({
              url: image_urls_array[img_id],
              focalPoint: {
                x: parseInt(x) / 100,
                y: parseInt(y) / 100
              },
              id: img_id,
              message: mes,
              social_url: url
            });
          }

          setAttributes({ image_data: new_image_data });
        }

        function onTitleChange(value) {
          setAttributes({ title: value });
        }

        function onDescriptionChange(value) {
          setAttributes({ description: value });
        }

        function onFocalPointChange(image_id, value) {

          let updated_image_data = [];
          let gallery_block_focus_points = {};

          image_data.map(function (object) {
            if (object.id === image_id) {
              let x = parseFloat(value.x).toFixed(2);
              let y = parseFloat(value.y).toFixed(2);

              updated_image_data.push({ url: object.url, social_url: object.social_url, focalPoint: { x, y }, id: image_id, message: object.message });
              gallery_block_focus_points[image_id] = (x * 100) + '% ' + (y * 100) + '%';

            } else {
              updated_image_data.push(object);
              let img_id = object.id;
              gallery_block_focus_points[img_id] = parseInt(object.focalPoint.x * 100) + '% ' + parseInt(object.focalPoint.y * 100) + '%';
            }
          });

          setAttributes({ gallery_block_focus_points: JSON.stringify(gallery_block_focus_points) });
          setAttributes({ image_data: updated_image_data });
        }

        function onMessageChange(image_id, value) {

          let updated_image_data = [];
          let messages = {};
          image_data.map(function (object) {
            if (object.id === image_id) {
              updated_image_data.push({ url: object.url, social_url: object.social_url, focalPoint: object.focalPoint, id: image_id, message: value });
              messages[image_id] = value;
            } else {
              updated_image_data.push(object);
              let img_id = object.id;
              messages[img_id] = object.message;
            }
          });
          setAttributes({ messages: JSON.stringify(messages) });
          setAttributes({ image_data: updated_image_data });
        }

        function onURLChange(image_id, value) {

          let updated_image_data = [];
          let urls = {};
          image_data.map(function (object) {
            if (object.id === image_id) {
              updated_image_data.push({ url: object.url, focalPoint: object.focalPoint, id: image_id, message: object.message, social_url: value });
              urls[image_id] = value;
            } else {
              updated_image_data.push(object);
              let img_id = object.id;
              urls[img_id] = object.social_url;
            }
          });
          setAttributes({ urls: JSON.stringify(urls) });
          setAttributes({ image_data: updated_image_data });
        }

        function onSelectImage(value) {
          let image_ids = [];
          let image_data = [];
          for (const key in value) {
            image_ids.push(value[key].id);
            let img_id = value[key].id;
            let message = value[key].message;
            let surl = value[key].social_url;
            image_data.push({ url: value[key].url, focalPoint: { x: 0.5, y: 0.5 }, id: img_id, message: message, social_url: surl });
          }
          setAttributes({ multiple_image: image_ids.join(',') });
          setAttributes({ image_data: image_data });
        }

        function onRemoveImages() {
          setAttributes({ multiple_image: '' });
          setAttributes({ gallery_block_focus_points: '' });
          setAttributes({ image_data: [] });
        }

        return <SocialMediaCards
          {...attributes}
          isSelected={isSelected}
          onTitleChange={onTitleChange}
          onDescriptionChange={onDescriptionChange}
          onSelectImage={onSelectImage}
          onMessageChange={onMessageChange}
          onURLChange={onURLChange}
          onFocalPointChange={onFocalPointChange}
          onRemoveImages={onRemoveImages}
        />
      }),
      save() {
        return null;
      }
    });
  };
}

