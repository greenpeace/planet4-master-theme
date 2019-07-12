import { SubmenuIcon } from './SubmenuIcon.js';
import { Submenu } from './Submenu.js';

export class SubmenuBlock {
    constructor() {
				const { registerBlockType } = wp.blocks;
				const { withSelect } = wp.data;

        registerBlockType( 'planet4-blocks/submenu', {
            title: 'Submenu',
            icon: SubmenuIcon,
						category: 'planet4-blocks',
						transforms: {
							from: [
								{
										type: 'shortcode',
										// Shortcode tag can also be an array of shortcode aliases
										tag: 'shortcake_submenu',
										attributes: {
											submenu_style: {
												type: 'integer',
												shortcode: ( { named: {submenu_style = '1' } } ) => submenu_style,
											},
											title: {
												type: 'string',
												shortcode: ( { named: { title = '' } } ) => title,
											},
											// description: {
											// 	type: 'string',
											// 	shortcode: ( { named: { description = '' } } ) => description,
											// },
										},
								},
							]
						},
						attributes: {
							heading1: {
								type: 'integer',
								default: 0
							},
              link1: {
								type: 'boolean',
							},
              style1: {
								type: 'string',
								default: 'none'
							},

              heading2: {
								type: 'integer',
								default: 0
							},
              link2: {
								type: 'boolean',
							},
              submenu_style: {
								type: 'integer',
								default: 1
							},
              title: {
								type: 'string',
							},
						},
						edit: withSelect( ( select ) => {
							const tagsTaxonomy = 'post_tag';
							const postTypesTaxonomy = 'p4-page-type';
							const args = {
								hide_empty: false,
							};
							const { getEntityRecords } = select( 'core' );
							// const tagsList = getEntityRecords( 'taxonomy', tagsTaxonomy, args );
							const postTypesList = getEntityRecords( 'taxonomy', postTypesTaxonomy );
							const posts = getEntityRecords( 'postType', 'post' );

							return {
								// postTypesList,
								// tagsList,
								// posts
							};
						} )( ( {
							isSelected,
							attributes,
							setAttributes
						} ) => {

								function onRowsChange( value ) {
									setAttributes( { covers_view: value } );
								}

								function onTitleChange( value ) {
									setAttributes( { title: value } );
								}

								function onHeadingChange( value ) {
									setAttributes( { heading1: value } );
								}

								function onSelectedLayoutChange( value ) {
									setAttributes( { cover_type: Number(value) } );
								}

								return <Submenu
								  { ...attributes }
									isSelected={ isSelected }
									// tagsList={ tagsList }
									// postTypesList={ postTypesList }
									// posts={ posts }
									// onSelectedTagsChange={ onSelectedTagsChange }
									onSelectedLayoutChange={ onSelectedLayoutChange }
									onTitleChange={ onTitleChange }
									onHeadingChange={ onHeadingChange }
									onRowsChange={ onRowsChange } />
						} ),
            save() {
							return null;
            }
        } );
    };
}

