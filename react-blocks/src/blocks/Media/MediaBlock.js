import {Media} from './Media';

const {__} = wp.i18n;

export class MediaBlock {
    constructor() {
      const {registerBlockType} = wp.blocks;

      registerBlockType('planet4-blocks/media-video', {
        title: __('Media block', 'p4ge'),
        icon: 'format-video',
        category: 'planet4-blocks',
        /**
         * Transforms old 'shortcake' shortcode to new gutenberg block.
         *
         * old block-shortcode:
         * [shortcake_media_video video_title="Lorem Ipsum"
         *                        description="Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged."
         *                        youtube_id="http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4"
         *                        video_poster_img="23241"
         * /]
         *
         * new block-gutenberg:
         * <!-- wp:planet4-blocks/media-video {"video_title":"Lorem Ipsum","description":"Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged.","youtube_id":"http://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4","video_poster_img":23241} /-->
         *
         */
        transforms: {
          from: [
            {
              type: 'shortcode',
              // Shortcode tag can also be an array of shortcode aliases
              tag: 'shortcake_media_video',
              attributes: {
                video_title: {
                  type: 'string',
                  shortcode: ({named: {video_title = ''}}) => video_title,
                },
                description: {
                  type: 'string',
                  shortcode: ({named: {description = ''}}) => description,
                },
                youtube_id: {
                  type: 'string',
                  shortcode: ({named: {youtube_id = ''}}) => youtube_id,
                },
                video_poster_img: {
                  type: 'integer',
                  shortcode: ({named: {video_poster_img = ''}}) => video_poster_img,
                }
              },
            },
          ]
        },
        attributes: {
          video_title: {
            type: 'string'
          },
          description: {
            type: 'string'
          },
          youtube_id: {
            type: 'string'
          },
          video_poster_img: {
            type: 'integer'
          }
        },
        edit: ({isSelected, attributes, setAttributes}) => {
          function onTitleChange(value) {
            setAttributes({video_title: value});
          }

          function onDescriptionChange(value) {
            setAttributes({description: value});
          }

          function onMediaUrlChange(value) {
            setAttributes({youtube_id: value});
          }

          function onSelectImage({id}) {
            setAttributes({video_poster_img: id});
          }

          function onSelectURL({url}) {
            setAttributes({id: null});
          }

          function onUploadError({message}) {
            console.log(message);
          }

          return <Media
            {...attributes}
            isSelected={isSelected}
            onTitleChange={onTitleChange}
            onDescriptionChange={onDescriptionChange}
            onMediaUrlChange={onMediaUrlChange}
            onSelectImage={onSelectImage}
            onSelectURL={onSelectURL}
            onUploadError={onUploadError}
          />
        },
        save() {
          return null;
        }
      });
    };
}
