import {Gallery} from './Gallery';

const {__} = wp.i18n;

export class GalleryBlock {
    constructor() {
      const {registerBlockType} = wp.blocks;
      const {__} = wp.i18n;
      const { withSelect } = wp.data;

      registerBlockType( 'planet4-blocks/gallery', {
        title: __('Gallery', 'p4ge'),
        icon: 'format-gallery',
        category: 'planet4-blocks',
        /**
         * Transforms old 'shortcake' shortcode to new gutenberg block.
         *
         * old block-shortcode:
         * [shortcake_gallery gallery_block_style="2" gallery_block_title="test title"
         *                    gallery_block_description="test desc" multiple_image="23603,23602,23600,23596"
         *                    gallery_block_focus_points="{'23596':'left top','23597':'left top','23599':'left top'}"
         * /]
         *
         * new block-gutenberg:
         * <!-- wp:planet4-blocks/gallery {"gallery_block_style":3,"gallery_block_title":"test title","gallery_block_description":"test desc",
         *      "multiple_image":"23603,23602,23600,23596"} /-->
         *
         */
        transforms: {
          from: [
            {
              type: 'shortcode',
              // Shortcode tag can also be an array of shortcode aliases
              tag: 'shortcake_gallery',
              attributes: {
                gallery_block_style: {
                  type: 'integer',
                  shortcode: ({named: {gallery_block_style = ''}}) => Number(gallery_block_style),
                },
                gallery_block_title: {
                  type: 'string',
                  shortcode: ({named: {gallery_block_title = ''}}) => gallery_block_title,
                },
                gallery_block_description: {
                  type: 'string',
                  shortcode: ({named: {gallery_block_description = ''}}) => gallery_block_description,
                },
                multiple_image: {
                  type: 'string',
                  shortcode: ({named: {multiple_image = ''}}) => multiple_image,
                },
                gallery_block_focus_points: {
                  type: 'string',
                  shortcode: ({named: {gallery_block_focus_points = ''}}) => gallery_block_focus_points,
                },
              },
            },
          ]
        },
        attributes: {
          gallery_block_style: {
            type: 'number',
            default: 1
          },
          gallery_block_title: {
            type: 'string',
          },
          gallery_block_description: {
            type: 'string',
          },
          multiple_image: {
            type: 'string',
          },
          gallery_block_focus_points: {
            type: 'string',
          },
          image_data: {
            type: 'object',
            default: []
          },
        },
        edit: withSelect( ( select, props ) => {

          const { attributes } = props;
          const { multiple_image } = attributes;

          let image_urls_array = [];

          if ( multiple_image ) {
            let image_id_array = multiple_image.split(',');

            $.each(image_id_array, function( index, img_id ) {
              let img_details = select('core').getMedia(img_id);
              if ( img_details ) {
                image_urls_array[img_id] = img_details.source_url;
              }
            });
          }

          return {
            image_urls_array
          };
        } )( ( {
          image_urls_array,
          isSelected,
          attributes,
          setAttributes
        } ) => {

          let {image_data,gallery_block_focus_points} = attributes;

          // Prepare image_data array on edit gallery block.
          if (0 == image_data.length && 0 < image_urls_array.length) {
            let new_image_data = [];
            let focal_points_json = gallery_block_focus_points ? JSON.parse(gallery_block_focus_points):{};

            for (const img_id in image_urls_array) {

              let x,y;
              if ($.isEmptyObject(focal_points_json) || !focal_points_json[img_id]) {
                [x,y] = [50,50];
              } else {
                [x,y] = focal_points_json[img_id].replace(/\%/g, '').split(' ');
              }

              new_image_data.push({
                url: image_urls_array[img_id],
                focalPoint: {
                  x:parseInt(x)/100,
                  y:parseInt(y)/100
                },
                id: img_id
              });
            }

            setAttributes({image_data: new_image_data});
          }

          function onTitleChange( value ) {
            setAttributes({gallery_block_title: value});
          }

          function onDescriptionChange( value ) {
            setAttributes({gallery_block_description: value });
          }

          function onSelectImage(value) {
            let image_ids = [];
            let image_data = [];
            for (const key in value) {
              image_ids.push(value[key].id);
              let img_id = value[key].id;
              image_data.push({url: value[key].url, focalPoint: {x:0.5, y:0.5}, id: img_id});
            }
            setAttributes({multiple_image: image_ids.join(',')});
            setAttributes({image_data: image_data});
          }

          function onSelectedLayoutChange( value ) {
            setAttributes({gallery_block_style: Number(value)});
          }

          function onFocalPointChange( image_id, value ) {

            let updated_image_data = [];
            let gallery_block_focus_points = {};

            image_data.map(function(object){
              if (object.id === image_id) {
                let x = parseFloat(value.x).toFixed(2);
                let y = parseFloat(value.y).toFixed(2);

                updated_image_data.push({url: object.url, focalPoint: {x,y}, id: image_id});
                gallery_block_focus_points[image_id] = (x*100)+'% '+(y*100)+'%';

              } else {
                updated_image_data.push(object);
                let img_id = object.id;
                gallery_block_focus_points[img_id] = parseInt(object.focalPoint.x*100)+'% '+parseInt(object.focalPoint.y*100)+'%';
              }
            });

            setAttributes({gallery_block_focus_points: JSON.stringify(gallery_block_focus_points)});
            setAttributes({image_data: updated_image_data});
          }

          function onRemoveImages() {
            setAttributes({multiple_image: ''});
            setAttributes({gallery_block_focus_points: ''});
            setAttributes({image_data: []});
          }

          function onDeleteImage(img_id) {
            let image_ids  = attributes.multiple_image;
            let image_data = attributes.image_data;
            if (image_ids)
              image_ids = image_ids.split(',');

            image_ids  = image_ids.filter(function(value, index, arr){ return parseInt(value) !== parseInt(img_id); });
            image_data = image_data.filter(function(value, index, arr){ return parseInt(value.id) !== parseInt(img_id); });

            setAttributes({multiple_image: image_ids.join(',')});
            setAttributes({image_data: image_data});
          }

          return <Gallery
            {...attributes}
            isSelected={isSelected}
            onSelectedLayoutChange={onSelectedLayoutChange}
            onSelectImage={onSelectImage}
            onTitleChange={onTitleChange}
            onDescriptionChange={onDescriptionChange}
            onFocalPointChange={onFocalPointChange}
            onRemoveImages={onRemoveImages}
            onDeleteImage={onDeleteImage}
          />
        }),
        save() {
          return null;
        }
      });
    };
}
