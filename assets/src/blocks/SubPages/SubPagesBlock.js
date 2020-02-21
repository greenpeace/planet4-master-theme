import { SubPages } from './SubPages';

export class SubPagesBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;
    const { withSelect } = wp.data;

    registerBlockType( 'planet4-blocks/sub-pages', {
      title: 'Sub Pages',
      icon: 'welcome-widgets-menus',
      category: 'planet4-blocks-beta',
      supports: {
        multiple: false, // Use the block just once per post.
      },
      attributes: {},
      save() {
        return null;
      },
      edit: withSelect( ( select ) => {

      } )( ( {
               isSelected,
               attributes,
               setAttributes
             } ) => {
        return <SubPages/>;
      } ),
    } );
  };
}

