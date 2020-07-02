import { Component, Fragment } from '@wordpress/element';
import {
  CheckboxControl,
  TextControl as BaseTextControl,
  PanelBody
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import TagSelector from '../../components/TagSelector/TagSelector';
import PostSelector from '../../components/PostSelector/PostSelector';
import PostTypeSelector from '../../components/PostTypeSelector/PostTypeSelector';
import { URLInput } from "../../components/URLInput/URLInput";
import { ArticlesFrontend } from "./ArticlesFrontend";

const { RichText } = wp.blockEditor;
const { __ } = wp.i18n;

const TextControl = withCharacterCounter(BaseTextControl);

export class ArticlesEditor extends Component {
  constructor(props) {
    super(props);

    this.toAttribute = this.toAttribute.bind(this);
  }

  toAttribute(attributeName) {
    const { setAttributes } = this.props;
    return value => setAttributes({ [attributeName]: value });
  }

  renderEdit() {
    const { attributes } = this.props;

    return (
      <Fragment>
        <InspectorControls>
          <PanelBody title={__('Setting', 'p4ge')}>
            <URLInput
              label={__('Button Link', 'p4ge')}
              value={attributes.read_more_link}
              onChange={this.toAttribute('read_more_link')}
            />
            <CheckboxControl
              label={__('Open in a new Tab', 'p4ge')}
              help={__('Open button link in new tab', 'p4ge')}
              value={attributes.button_link_new_tab}
              checked={attributes.button_link_new_tab}
              onChange={this.toAttribute('button_link_new_tab')}
            />
            {attributes.posts !== 'undefined' && attributes.posts.length === 0 &&
              <Fragment>
                <TextControl
                  label={__('Articles count', 'p4ge')}
                  help={__('Number of articles', 'p4ge')}
                  type="number"
                  value={attributes.article_count}
                  onChange={value => this.toAttribute('article_count')(Number(value))}
                />
                <TagSelector
                  value={attributes.tags}
                  onChange={this.toAttribute('tags')}
                />
                <p className='FieldHelp'>Associate this block with Actions that have specific Tags</p>
                <PostTypeSelector
                  label={__('Post Types', 'p4ge')}
                  value={attributes.post_types}
                  onChange={this.toAttribute('post_types')}
                />
                <div className="ignore-categories-wrapper">
                  <CheckboxControl
                    label={__('Ignore categories', 'p4ge')}
                    help={__('Ignore categories when filtering posts to populate the content of this block', 'p4ge')}
                    value={attributes.ignore_categories}
                    checked={attributes.ignore_categories}
                    onChange={this.toAttribute('ignore_categories')}
                  />
                </div>
              </Fragment>
            }
            {attributes.tags.length === 0 && attributes.post_types.length === 0 &&
              <div>
                <hr />
                <label>{__('Manual override', 'p4ge')}</label>
                <PostSelector
                  value={attributes.posts}
                  onChange={this.toAttribute('posts')}
                  label={__('CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains in tact.', 'p4ge')}
                  maxLength={10}
                  maxSuggestions={20}
                  postType='post'
                />
              </div>
            }
          </PanelBody>
        </InspectorControls>
      </Fragment>
    );
  }

  renderView() {
    const { attributes, postType } = this.props;

    return (
      <Fragment>
        <header>
          <RichText
            tagName="h2"
            className="page-section-header"
            placeholder={__('Enter title', 'p4ge')}
            value={attributes.article_heading}
            onChange={this.toAttribute('article_heading')}
            keepPlaceholderOnFocus={true}
            withoutInteractiveFormatting
            characterLimit={40}
          />
        </header>
        <RichText
          tagName="p"
          className="page-section-description"
          placeholder={__('Enter description', 'p4ge')}
          value={attributes.articles_description}
          onChange={this.toAttribute('articles_description')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
          characterLimit={200}
        />
        <ArticlesFrontend isEditing postType={postType} {...attributes} />
        <RichText
          tagName="div"
          className="btn btn-secondary btn-block article-load-more load-more"
          placeholder={__('Override button text', 'p4ge')}
          value={attributes.read_more_text}
          onChange={this.toAttribute('read_more_text')}
          keepPlaceholderOnFocus={true}
          withoutInteractiveFormatting
        />
      </Fragment>
    );
  }

  render() {
    return (
      <div>
        {
          this.props.isSelected && this.renderEdit()
        }
        {this.renderView()}
      </div>
    );
  }
}
