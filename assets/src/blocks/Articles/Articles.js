import {Component, Fragment} from '@wordpress/element';
import {Preview} from '../../components/Preview';
import {
  CheckboxControl,
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender
} from '@wordpress/components';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import TagSelector from '../../components/TagSelector/TagSelector';
import PostSelector from '../../components/PostSelector/PostSelector';
import PostTypeSelector from '../../components/PostTypeSelector/PostTypeSelector';


const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Articles extends Component {
  renderEdit() {
    const {__} = wp.i18n;

    const { attributes, setAttributes } = this.props;

    const toAttribute = attributeName => value => {
      setAttributes( { [ attributeName ]: value } );
    };

    return (
      <Fragment>
        <div>
          <TextControl
            label={__('Title', 'p4ge')}
            placeholder={__('Enter title', 'p4ge')}
            help={__('Your default is set to [ Latest Articles ]', 'p4ge')}
            value={attributes.article_heading}
            onChange={ toAttribute('article_heading')}
            characterLimit={40}
          />
        </div>


        <div>
          <TextareaControl
            label={__('Description', 'p4ge')}
            placeholder={__('Enter description', 'p4ge')}
            value={attributes.articles_description}
            onChange={ toAttribute('articles_description')}
            characterLimit={200}
          />
        </div>

        <div>
          <TextControl
            label={__('Button Text', 'p4ge')}
            placeholder={__('Override button text', 'p4ge')}
            help={__('Your default is set to [ Load More ]', 'p4ge')}
            value={attributes.read_more_text}
            onChange={ toAttribute('read_more_text')}
          />
        </div>


        <div>
          <TextControl
            label={__('Button Link', 'p4ge')}
            placeholder={__('Add read more button link', 'p4ge')}
            value={attributes.read_more_link}
            onChange={ toAttribute('read_more_link')}
          />
        </div>

        <div>
          <CheckboxControl
            label={__('Open in a new Tab', 'p4ge')}
            help={__('Open button link in new tab', 'p4ge')}
            value={attributes.button_link_new_tab}
            checked={attributes.button_link_new_tab}
            onChange={ toAttribute('button_link_new_tab')}
          />
        </div>

        {
          attributes.posts !== 'undefined' && attributes.posts.length === 0
            ?
            <Fragment>
              <div>
                <TextControl
                  label={__('Articles count', 'p4ge')}
                  help={__('Number of articles', 'p4ge')}
                  type="number"
                  value={attributes.article_count}
                  onChange={toAttribute('article_count')}
                />
              </div>
              <div>
                <TagSelector
                  value={ attributes.tags }
                  onChange={ toAttribute('tags')}
                />
                <p className='FieldHelp'>Associate this block with Actions that have specific Tags</p>
              </div>
              <div>
                <PostTypeSelector
                  label={__('Post Types', 'p4ge')}
                  value={attributes.post_types}
                  onChange={toAttribute('post_types')}
                />
              </div>
              <div className="ignore-categories-wrapper">
                <CheckboxControl
                  label={__('Ignore categories', 'p4ge')}
                  help={__('Ignore categories when filtering posts to populate the content of this block', 'p4ge')}
                  value={attributes.ignore_categories}
                  checked={attributes.ignore_categories}
                  onChange={ toAttribute('ignore_categories')}
                />
              </div>
            </Fragment>
            : null
        }

        {
          (attributes.tags.length === 0 && attributes.post_types.length === 0) &&
          <div>
            <hr/>
            <label>{ __( 'Manual override', 'p4ge' ) }</label>
            <PostSelector
              value={ attributes.posts }
              onChange={toAttribute('posts')}
              label={ __( 'CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains in tact.', 'p4ge' ) }
              maxLength="10"
              maxSuggestions="20"
              postType={'post'}
            />
          </div>
        }

      </Fragment>
    );
  }

  render() {
    return (
      <div>
        {
          !!this.props.isSelected && this.renderEdit()
        }
        <Preview showBar={ this.props.isSelected }>
          <ServerSideRender
            block={ 'planet4-blocks/articles' }
            attributes={ this.props.attributes }>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
