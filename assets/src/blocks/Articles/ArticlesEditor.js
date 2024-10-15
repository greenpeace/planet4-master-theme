import {
  CheckboxControl,
  TextControl as BaseTextControl,
  PanelBody,
  Tooltip,
} from '@wordpress/components';
import {InspectorControls, RichText} from '@wordpress/block-editor';
import withCharacterCounter from '../components/withCharacterCounter/withCharacterCounter';
import TagSelector from '../../block-editor/TagSelector';
import {PostSelector} from '../../block-editor/PostSelector';
import PostTypeSelector from '../../block-editor/PostTypeSelector';
import {URLInput} from '../../block-editor/URLInput/URLInput';
import {ArticlesList} from './ArticlesList';
import {useArticlesFetch} from './useArticlesFetch';
import {useSelect} from '@wordpress/data';

const {__} = wp.i18n;

const TextControl = withCharacterCounter(BaseTextControl);

const renderEdit = (attributes, toAttribute) => {
  return (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <TextControl
          label={__('Button Text', 'planet4-blocks-backend')}
          placeholder={__('Override button text', 'planet4-blocks-backend')}
          help={__('Your default is set to [ Load more ]', 'planet4-blocks-backend')}
          value={attributes.read_more_text}
          onChange={toAttribute('read_more_text')}
        />
        <URLInput
          label={__('Button Link', 'planet4-blocks-backend')}
          value={attributes.read_more_link}
          onChange={toAttribute('read_more_link')}
        />
        <CheckboxControl
          label={__('Open in a new Tab', 'planet4-blocks-backend')}
          help={__('Open button link in new tab', 'planet4-blocks-backend')}
          value={attributes.button_link_new_tab}
          checked={attributes.button_link_new_tab}
          onChange={toAttribute('button_link_new_tab')}
        />
        <TextControl
          label={__('Articles count', 'planet4-blocks-backend')}
          help={__('Number of articles', 'planet4-blocks-backend')}
          type="number"
          min={1}
          value={attributes.article_count}
          onChange={value =>
            toAttribute('article_count')(Number(value))}
        />
        {attributes.posts !== 'undefined' && attributes.posts.length === 0 &&
          <>
            <TagSelector
              value={attributes.tags}
              onChange={toAttribute('tags')}
            />
            <p className="FieldHelp">Associate this block with Actions that have specific Tags</p>
            <PostTypeSelector
              label={__('Post Types', 'planet4-blocks-backend')}
              value={attributes.post_types}
              onChange={toAttribute('post_types')}
            />
            <div className="ignore-categories-wrapper">
              <CheckboxControl
                label={__('Ignore categories', 'planet4-blocks-backend')}
                help={__('Ignore categories when filtering posts to populate the content of this block', 'planet4-blocks-backend')}
                value={attributes.ignore_categories}
                checked={attributes.ignore_categories}
                onChange={toAttribute('ignore_categories')}
              />
            </div>
          </>
        }
        {attributes.tags.length === 0 && attributes.post_types.length === 0 &&
          <div>
            <hr />
            <p>{__('Manual override', 'planet4-blocks-backend')}</p>
            <PostSelector
              label={__('CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains intact.', 'planet4-blocks-backend')}
              selected={attributes.posts || []}
              onChange={toAttribute('posts')}
              postType="post"
              placeholder={__('Select articles', 'planet4-blocks-backend')}
              maxLength={10}
              maxSuggestions={20}
            />
          </div>
        }
      </PanelBody>
    </InspectorControls>
  );
};

const renderView = ({attributes, postType, posts, totalPosts}, toAttribute) => {
  const hasMultiplePages = totalPosts > attributes.article_count;

  return (
    <>
      <Tooltip text={__('Edit text', 'planet4-blocks-backend')}>
        <header className="articles-title-container">
          <RichText
            tagName="h2"
            className="page-section-header"
            placeholder={__('Enter title', 'planet4-blocks-backend')}
            value={attributes.article_heading}
            onChange={toAttribute('article_heading')}
            withoutInteractiveFormatting
            allowedFormats={[]}
          />
        </header>
      </Tooltip>
      <RichText
        tagName="p"
        className="page-section-description"
        placeholder={__('Enter description', 'planet4-blocks-backend')}
        value={attributes.articles_description}
        onChange={toAttribute('articles_description')}
        withoutInteractiveFormatting
        allowedFormats={['core/bold', 'core/italic']}
      />
      <ArticlesList posts={posts} postType={postType} />
      {attributes.posts.length === 0 && hasMultiplePages && (
        <Tooltip text={__('Edit text', 'planet4-blocks-backend')}>
          <div className="btn btn-secondary article-load-more">
            <RichText
              tagName="div"
              placeholder={__('Enter text', 'planet4-blocks-backend')}
              value={attributes.read_more_text}
              onChange={toAttribute('read_more_text')}
              withoutInteractiveFormatting
              allowedFormats={[]}
            />
          </div>
        </Tooltip>
      )
      }
    </>
  );
};

export const ArticlesEditor = props => {
  const {isSelected, attributes, setAttributes} = props;

  const {postType, postId, postCategories} = useSelect(select => ({
    postType: select('core/editor').getCurrentPostType(),
    postId: select('core/editor').getCurrentPostId(),
    postCategories: select('core/editor').getEditedPostAttribute('categories'),
  }), []);

  const {posts, totalPosts} = useArticlesFetch(attributes, postType, postId, postCategories);

  const toAttribute = attributeName => value => setAttributes({[attributeName]: value});

  return (
    <div>
      {isSelected && renderEdit(attributes, toAttribute)}
      {renderView({attributes, postType, posts, totalPosts}, toAttribute)}
    </div>
  );
};
