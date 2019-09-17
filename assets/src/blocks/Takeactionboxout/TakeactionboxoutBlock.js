import {Takeactionboxout} from './Takeactionboxout.js';

export class TakeactionboxoutBlock {
	constructor() {
		const {registerBlockType} = wp.blocks;
		const {__} = wp.i18n;
		const {withSelect} = wp.data;

		registerBlockType('planet4-blocks/take-action-boxout', {
			title: __('Take Action Boxout'),
			icon: 'welcome-widgets-menus',
			category: 'planet4-blocks',
			supports: {
				multiple: false, // Use the block just once per post.
			},

			// Transform the shortcode into a Gutenberg block
			// this is used when a user clicks "Convert to blocks"
			// on the "Classic Editor" block
			transforms: {
				from: [
					{
						type: 'shortcode',
						// Shortcode tag can also be an array of shortcode aliases
						tag: 'shortcake_take_action_boxout',
						attributes: {
							take_action_page: {
								type: 'integer',
								shortcode: function (attributes) {
									return Number(attributes.named.take_action_page);
								}
							},
							custom_title: {
								type: 'string',
								shortcode: function (attributes) {
									return attributes.named.custom_title;
								}
							},
							custom_excerpt: {
								type: 'string',
								shortcode: function (attributes) {
									return attributes.named.custom_excerpt;
								}
							},
							custom_link: {
								type: 'string',
								shortcode: function (attributes) {
									return attributes.named.custom_link;
								}
							},
							custom_link_text: {
								type: 'string',
								shortcode: function (attributes) {
									return attributes.named.custom_link_text;
								}
							},
							custom_link_new_tab: {
								type: 'boolean',
								shortcode: function (attributes) {
									return attributes.named.custom_link_new_tab;
								}
							},
							tag_ids: {
								type: 'array',
								shortcode: function (attributes) {
									return attributes.named.tag_ids ?
                    attributes.named.tag_ids.split(',').map(tag => Number(tag)).filter(tag => tag > 0)
                    : [];
								}
							},
							background_image: {
								type: 'integer',
								shortcode: ({named: {background_image = ''}}) => Number(background_image) > 0 ? Number(background_image) : 0,
							},
						},
					},
				]
			},
			// This attributes definition mimics the one in the PHP side.
			attributes: {
				take_action_page: {
					type: 'number',
				},
				custom_title: {
					type: 'string',
				},
				custom_excerpt: {
					type: 'string',
				},
				custom_link: {
					type: 'string',
				},
				custom_link_text: {
					type: 'string',
				},
				custom_link_new_tab: {
					type: 'boolean',
					default: false
				},
				tag_ids: {
					type: 'array',
					default: []
				},
				background_image: {
					type: 'number',
				},
			},
			// withSelect is a "Higher Order Component", it works as
			// a Decorator, it will provide some basic API functionality
			// through `select`.
			edit: withSelect( ( select, props ) => {
				const tagsTaxonomy = 'post_tag';
				const actPage = 'page';

				const args = {
					hide_empty: false,
					per_page: 50,
				};
				const {getEntityRecords, getMedia} = select('core');

				const tagsList = getEntityRecords('taxonomy', tagsTaxonomy, args);

				const act_page_args = {
					per_page: -1,
					sort_order: 'asc',
					sort_column: 'post_title',
					parent: window.p4ge_vars.planet4_options.act_page,
					post_status: 'publish',
				};

				const actPageList = getEntityRecords('postType', actPage, act_page_args);

				const { attributes } = props;
				const { background_image } = attributes;

				let background_image_url = '';
				if (background_image) {
					background_image_url = select('core').getMedia(background_image);
					if ( background_image_url ) {
						background_image_url = background_image_url.media_details.sizes.medium.source_url;
					}
				}

				return {
					actPageList,
					tagsList,
					background_image_url,
				};
			} )( ( {
				tagsList,
				actPageList,
				background_image_url,
				isSelected,
				attributes,
				setAttributes
			} ) => {

				if (!tagsList || !actPageList) {
					return "Populating block's fields...";
				}

				if ((tagsList && tagsList.length === 0) || (actPageList && actPageList.length === 0)) {
					return "Populating block's fields...";
				}

				// These methods are passed down to the
				// Articles component, they update the corresponding attribute.
				function onSelectTakeActoinPage( value ) {
					setAttributes({ take_action_page: value });
				}

				function onCustomTitleChange(value) {
					setAttributes({custom_title: value});
				}

				function onCustomExcerptChange(value) {
					setAttributes({custom_excerpt: value});
				}

				function onCustomLinkChange(value) {
					setAttributes({custom_link: value});
				}

				function onCustomLinkTextChange(value) {
					setAttributes({custom_link_text: value});
				}

				function onCustomLinkNewTabChange(value) {
					setAttributes({custom_link_new_tab: value});
				}

				function onButtonLinkTabChange(value) {
					setAttributes({button_link_new_tab: value});
				}

				function onSelectedTagsChange(tagIds) {
					setAttributes({tag_ids: tagIds});
				}

				function onSelectBackGroundImage( {id} ) {
					setAttributes({background_image: id});
				}

				function onRemoveBackGroundImage() {
					setAttributes({background_image: -1});
				}

				// We pass down all the attributes to Covers as props using
				// the spread operator. Then we selectively add more
				// props.
				return <Takeactionboxout
					{...attributes}
					isSelected={isSelected}
					tagsList={tagsList}
					actPageList={actPageList}
					background_image_url={background_image_url}
					onSelectTakeActoinPage={onSelectTakeActoinPage}
					onCustomTitleChange={onCustomTitleChange}
					onCustomExcerptChange={onCustomExcerptChange}
					onCustomLinkChange={onCustomLinkChange}
					onCustomLinkTextChange={onCustomLinkTextChange}
					onCustomLinkNewTabChange={onCustomLinkNewTabChange}
					onButtonLinkTabChange={onButtonLinkTabChange}
					onSelectedTagsChange={onSelectedTagsChange}
					onSelectBackGroundImage={onSelectBackGroundImage}
					onRemoveBackGroundImage={onRemoveBackGroundImage}/>
			}),
			save() {
				return null;
			}
		} );
	};
}

