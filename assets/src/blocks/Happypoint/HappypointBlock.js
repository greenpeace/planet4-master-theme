import {Happypoint} from './Happypoint';

export class HappypointBlock {
    constructor() {
      const {registerBlockType} = wp.blocks;
      const {__} = wp.i18n;
      const { withSelect } = wp.data;

      registerBlockType( 'planet4-blocks/happypoint', {
        title: __('Happypoint', 'p4ge'),
        icon: 'format-image',
        category: 'planet4-blocks',
        attributes: {
          focus_image: {
            type: 'string',
          },
          opacity: {
            type: 'number',
            default: 60
          },
          mailing_list_iframe: {
            type: 'boolean',
          },
          iframe_url: {
            type: 'string',
          },
          id: {
            type: 'number',
          },
          load_iframe: {
            type: 'boolean',
            default: false
          }
        },
        edit: withSelect( ( select, props ) => {
          const { attributes } = props;
          const { id } = attributes;
          let img_url = '';

          if (id && (0 < id)) {
            img_url = select('core').getMedia(id);
            if(img_url){
              img_url = img_url.source_url;
            }
          }

          return {
            img_url
          };
        } )( ( {
          img_url,
          isSelected,
          attributes,
          setAttributes
        } ) => {
          function onBackgroundChange( value ) {
            setAttributes({background: value});
          }

          function onOpacityChange( value ) {
            setAttributes({opacity: value});
          }

          function onMailingListIframeChange( value ) {
            setAttributes({mailing_list_iframe: value});
          }

          function onIframeUrlChange( value ) {
            setAttributes({iframe_url: value});
          }

          function onFocalPointChange( {x,y} ) {
            x = parseFloat(x).toFixed(2);
            y = parseFloat(y).toFixed(2);
            setAttributes({focus_image: (x*100)+'% '+(y*100)+'%'});
          }

          function onSelectImage({id}) {
            setAttributes({id});
          }

          function onRemoveImages() {
            setAttributes({id: -1});
            setAttributes({focus_image: ''});
          }

          return <Happypoint
            {...attributes}
            isSelected={isSelected}
            url={img_url}
            onSelectImage={onSelectImage}
            onOpacityChange={onOpacityChange}
            onMailingListIframeChange={onMailingListIframeChange}
            onIframeUrlChange={onIframeUrlChange}
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

