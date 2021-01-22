import { Covers } from './Covers.js';

export class CoversBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { withSelect } = wp.data;

    registerBlockType('planet4-blocks/covers', {
      title: 'Covers',
      icon: 'slides',
      category: 'planet4-blocks',
      // This attributes definition mimics the one in the PHP side.
      attributes: {
        title: {
          type: 'string',
        },
        description: {
          type: 'string',
        },
        tags: {
          type: 'array',
          default: []
        },
        posts: {
          type: 'array',
          default: []
        },
        post_types: {
          type: 'array',
          default: []
        },
        covers_view: {
          type: 'string',
          default: '1'
        },
        cover_type: {
          type: 'string',
        }
      },
      // withSelect is a "Higher Order Component", it works as
      // a Decorator, it will provide some basic API functionality
      // through `select`.
      edit: withSelect((select) => {
        const currentPostType = select('core/editor').getCurrentPostType();

        return {
          currentPostType,
        };
      })(({
        currentPostType,
        isSelected,
        attributes,
        setAttributes
      }) => {

        if (!currentPostType) {
          return "Populating block's fields...";
        }

        return <Covers
          attributes={attributes}
          setAttributes={setAttributes}
          isSelected={isSelected}
          currentPostType={currentPostType}
        />
      }),
      save() {
        return null;
      }
    });
  };
}

