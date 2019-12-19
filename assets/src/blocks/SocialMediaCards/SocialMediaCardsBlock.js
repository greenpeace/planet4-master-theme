import { SocialMediaCards } from './SocialMediaCards';

export class SocialMediaCardsBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { __ } = wp.i18n;
    const { withSelect } = wp.data;

    registerBlockType( 'planet4-blocks/social-media-cards', {
      title: __( 'Social Media Cards', 'planet4-blocks-backend' ),
      icon: 'format-image',
      category: 'planet4-blocks',
      attributes: {
        title: {
          type: 'string',
          default: '',
        },
        description: {
          type: 'string',
          default: '',
        },
        cards: {
          type: 'array',
          default: [],
          image_id: {
            type: 'int',
          },
          message: {
            type: 'string',
            default: '',
          },
          social_url: {
            default: '',
          },
        },
      },
      save() {
        return null;
      },
      edit: withSelect( ( select, props ) => {

        const { attributes } = props;

        let cards = attributes.cards.map( ( card ) => {
          const { image_id } = card;
          let imgDetails = select( 'core' ).getMedia( image_id );

          return {
            image_url: imgDetails ? imgDetails.source_url : null,
            ...card,
          };
        } );

        return {
          cards
        };

      } )( ( {
               cards,
               isSelected,
               attributes,
               setAttributes
             } ) => {

        function onTitleChange( value ) {
          setAttributes( { title: value } );
        }

        function onDescriptionChange( value ) {
          setAttributes( { description: value } );
        }

        function onMessageChange( index, value ) {
          let cards = [...attributes.cards];
          cards[ index ].message = value;
          setAttributes( { cards: cards } );
        }

        function onURLChange( index, value ) {
          let cards = [...attributes.cards];
          cards[ index ].social_url = value;
          setAttributes( { cards: cards } );
        }

        function onSelectImages( images ) {
          const imageIds = images.map( image => image.id );
          const newImageIds = imageIds.filter( id => !cards.some( card => card.image_id === id ) );
          const stillSelectedCards = cards.filter( card => imageIds.includes( card.image_id ) );

          const newCards = [
            ...stillSelectedCards,
            ...newImageIds.map( id => ({
              image_id: id,
              message: '',
              social_url: '',
            }) )
          ];

          setAttributes( {
            cards: newCards
          } );
        }

        function onDeleteImage( imageId ) {
          setAttributes( {
            cards: cards.filter( card => card.image_id !== imageId )
          } );
        }

        return <SocialMediaCards
          attributes={ attributes }
          cards={ cards }
          isSelected={ isSelected }
          onTitleChange={ onTitleChange }
          onDescriptionChange={ onDescriptionChange }
          onSelectImages={ onSelectImages }
          onMessageChange={ onMessageChange }
          onURLChange={ onURLChange }
          onDeleteImage={ onDeleteImage }
        />;
      } ),
    } );
  };
}

