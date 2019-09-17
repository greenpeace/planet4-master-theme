import {Component,Fragment} from "@wordpress/element";
import {BlockControls,MediaUpload,MediaUploadCheck} from "@wordpress/editor";
import {Preview} from '../../components/Preview';
import {ImageOrButton} from '../../components/ImageOrButton/ImageOrButton';
import {
	FormTokenField,
	TextControl,
	TextareaControl,
	ServerSideRender,
	ToggleControl,
	SelectControl,
	CheckboxControl,
	Toolbar,
	IconButton, Button
} from '@wordpress/components';

const {apiFetch} = wp;
const {addQueryArgs} = wp.url;

export class Takeactionboxout extends Component {
	constructor(props) {
		super(props);

		// Populate tag tokens for saved tags.
		let tagTokens = props.tagsList.filter(tag => props.tag_ids.includes(tag.id)).map(tag => tag.name);

		this.state = {
			tagTokens: tagTokens,
		};
	}

	onSelectedTagsChange(tokens) {
		const tagIds = tokens.map(token => {
			return this.props.tagsList.filter(tag => tag.name === token)[0].id;
		});
		this.props.onSelectedTagsChange(tagIds);
		this.setState({tagTokens: tokens})
	}

	isCustomised() {
		return ( this.props.custom_title || this.props.custom_excerpt || this.props.custom_link || this.props.custom_link_text || this.props.custom_link_new_tab || this.state.tagTokens.length > 0 || ( this.props.background_image && 0 < this.props.background_image ) );
	}

	takeActionPageSelected() {
		return ( this.props.take_action_page && this.props.take_action_page !== '0' );
	}

	renderEdit() {
		const tagSuggestions = this.props.tagsList.map(tag => tag.name);

		let actpage_list = this.props.actPageList.map((actPage) => ({label: actPage.title.raw, value: actPage.id}));
		const {__} = wp.i18n;

		actpage_list.unshift({label: __('--Select Take Action Page--'), value: 0});

		const getImageOrButton = (openEvent) => {
			if ( this.props.background_image && ( 0 < this.props.background_image ) ) {

				return (

					<div align='center'>
						<img
							src={ this.props.background_image_url }
							onClick={ openEvent }
							className='takeactionboxout-block-background-img'
							width={'400px'}
							style={{padding: '10px 10px'}}
						/>
					</div>

				);
			}
			else {
				return (
					<div className='button-container'>
						<Button
							onClick={ openEvent }
							className='button'
							disabled={this.takeActionPageSelected()}>
							+ {__('Select Background Image', 'p4ge')}
						</Button>
					</div>
				);
			}
		};

		return (
			<Fragment>
				<h3>{__('Take Action Boxout', 'p4ge')}</h3>
				<div>
					<SelectControl
						label={__('Select Take Action Page', 'p4ge')}
						value={this.props.take_action_page}
						options={actpage_list}
						onChange={this.props.onSelectTakeActoinPage}
						disabled={this.isCustomised()}
					/>
					<h5>{__('Or customise your take action boxout (if inserted in POSTS, the block will float on the side, if inserted in PAGES, it will appear in the page body)', 'p4ge')}</h5>
					<TextControl
						label={__('Custom Title', 'p4ge')}
						placeholder={__('Enter Title', 'p4ge')}
						value={this.props.custom_title}
						onChange={this.props.onCustomTitleChange}
						disabled={this.takeActionPageSelected()}
					/>
					<TextareaControl
						label={__('Custom Excerpt', 'p4ge')}
						placeholder={__('Enter Custom Excerpt', 'p4ge')}
						value={this.props.custom_excerpt}
						onChange={this.props.onCustomExcerptChange}
						disabled={this.takeActionPageSelected()}
					/>
					<TextControl
						label={__('Custom Link', 'p4ge')}
						placeholder={__('Enter Custom Link', 'p4ge')}
						value={this.props.custom_link}
						onChange={this.props.onCustomLinkChange}
						disabled={this.takeActionPageSelected()}
					/>
					<TextControl
						label={__('Custom Link Text', 'p4ge')}
						placeholder={__('Enter Custom Link Text', 'p4ge')}
						value={this.props.custom_link_text}
						onChange={this.props.onCustomLinkTextChange}
						disabled={this.takeActionPageSelected()}
					/>
					<CheckboxControl
						label={__('Open in a new Tab', 'p4ge')}
						help={__('Open custom link in new tab', 'p4ge')}
						value={this.props.custom_link_new_tab}
						checked={this.props.custom_link_new_tab}
						onChange={(e) => this.props.onCustomLinkNewTabChange(e)}
						disabled={this.takeActionPageSelected()}
					/>
					<FormTokenField
						value={this.state.tagTokens}
						suggestions={tagSuggestions}
						label={__('Select Tags', 'p4ge')}
						onChange={tokens => this.onSelectedTagsChange(tokens)}
						disabled={this.takeActionPageSelected()}
					/>
					<br></br>
					{__('Select Background Image', 'p4ge')}
					<div>
						<MediaUploadCheck>
							<MediaUpload
								title={__('Select Background Image', 'p4ge')}
								type='image'
								onSelect={this.props.onSelectBackGroundImage}
								value={this.props.background_image}
								allowedTypes={['image']}
								render={ ({ open }) => getImageOrButton(open) }
							/>
						</MediaUploadCheck>
					</div>
					<BlockControls>
						{ this.props.background_image && ( 0 < this.props.background_image ) && (
							<Toolbar>
								<IconButton
									className='components-icon-button components-toolbar__control'
									label={__('Remove Image', 'p4ge')}
									onClick={this.props.onRemoveBackGroundImage}
									icon='trash'
								/>
							</Toolbar>
						)}
					</BlockControls>
				</div>
				<hr />
			</Fragment>
		);
	}

	render() {
		return (
			<div>
				{
					this.props.isSelected
						? this.renderEdit()
						: null
				}
				<Preview showBar={this.props.isSelected}>
					<ServerSideRender
						block={'planet4-blocks/take-action-boxout'}
						attributes={{
							take_action_page: this.props.take_action_page,
							custom_title: this.props.custom_title,
							custom_excerpt: this.props.custom_excerpt,
							custom_link: this.props.custom_link,
							custom_link_text: this.props.custom_link_text,
							custom_link_new_tab: this.props.custom_link_new_tab,
							tag_ids: this.props.tag_ids,
							background_image: this.props.background_image,
						}}>
					</ServerSideRender>
				</Preview>
			</div>
		);
	}
}
