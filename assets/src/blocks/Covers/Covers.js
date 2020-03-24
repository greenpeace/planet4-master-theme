import { Component } from '@wordpress/element';

import {
  SelectControl,
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender } from '@wordpress/components';

import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';
import TagSelector from '../../components/TagSelector/TagSelector';
import PostSelector from '../../components/PostSelector/PostSelector';
import PostTypeSelector from '../../components/PostTypeSelector/PostTypeSelector';

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

const TYPE_TAKE_ACTION = '1';
const TYPE_CAMPAIGN = '2';
const TYPE_CONTENT = '3';

export class Covers extends Component {
    constructor(props) {
      super(props);

      const { __ } = wp.i18n;

      this.options = [{
          label: __('Content Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/content_covers.png',
          value: TYPE_CONTENT,
          help: __('Content covers pull the image from the post.')
        }];

      if ('campaign' !== props.currentPostType) {
        this.options.push({
          label: __('Take Action Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/take_action_covers.png',
          value: TYPE_TAKE_ACTION,
          help: __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button')
        });
        this.options.push({
          label: __('Campaign Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/campaign_covers.png',
          value: TYPE_CAMPAIGN,
          help: __('Campaign covers pull the associated image and hashtag from the system tag definitions.'),
        });
      }

      this.onLayoutChange = this.onLayoutChange.bind( this );
    }

    onLayoutChange( value ) {
      const { setAttributes } = this.props;

      // Post types are available only on cover_type 3, so we reset the post_types attribute in the other 2 cases.
      if ( [TYPE_TAKE_ACTION, TYPE_CAMPAIGN].includes( value ) ) {
        setAttributes( { post_types: [] } );
      }
      // Reset posts attribute when changing layout also.
      setAttributes({cover_type: value, posts: []});
    }

    renderEdit() {
      const { __ } = wp.i18n;

      const { attributes, setAttributes } = this.props;

      const toAttribute = attributeName => value => {
        setAttributes( { [ attributeName ]: value } );
      };

      return (
        <div>
          <h3>{ __('What style of cover do you need?', 'p4ge') }</h3>

          <div>
            <LayoutSelector
              selectedOption={ attributes.cover_type }
              onSelectedLayoutChange={ this.onLayoutChange }
              options={this.options}
            />
          </div>

          <div>
            <SelectControl
              label="Rows to display"
              value={ attributes.covers_view }
              options={ [
                { label: '1 Row', value: '1' },
                { label: '2 Rows', value: '2' },
                { label: 'All rows', value: '3' },
              ] }
              onChange={toAttribute('covers_view')}
            />
          </div>

          <div>
            <TextControl
              label="Title"
              placeholder="Enter title"
              value={ attributes.title }
              onChange={ toAttribute('title')}
              characterLimit={40}
            />
          </div>

          <div>
            <TextareaControl
              label="Description"
              placeholder="Enter description"
              value={ attributes.description }
              onChange={ toAttribute('description')}
              characterLimit={200}
            />
          </div>

          {
            attributes.posts !== 'undefined' && attributes.posts.length === 0
              ?
              <div>
                <TagSelector
                  value={ attributes.tags }
                  onChange={toAttribute('tags')}
                />
                <p
                  className='FieldHelp'>Associate this block with Actions that have specific Tags
                </p>
              </div>
              : null
          }

          {
            attributes.cover_type === TYPE_CONTENT && attributes.posts.length === 0
              ?
              <div>
                <PostTypeSelector
                  value={ attributes.post_types}
                  onChange={ toAttribute('post_types')}
                />
              </div>
              : null
          }

          {
            (attributes.cover_type === TYPE_CONTENT) &&
            (attributes.tags.length === 0 && attributes.post_types.length === 0)
              ? <div>
                <label>Manual override</label>
                <PostSelector
                  value={attributes.posts}
                  onChange={toAttribute('posts')}
                  placeholder="Select Articles"
                  postType={'post'}
                />
              </div>
              : null
          }

          {
            (attributes.cover_type === TYPE_TAKE_ACTION) &&
            (attributes.tags.length === 0 && attributes.post_types.length === 0)
              ? <div>
                <label>Manual override</label>
                <PostSelector
                  value={attributes.posts}
                  onChange={toAttribute('posts')}
                  placeholder="Select Articles"
                  postType={'act_page'}
                />
              </div>
              : null
          }

        </div>
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
              <Preview showBar={ this.props.isSelected }>
                <ServerSideRender
                  block={ 'planet4-blocks/covers' }
                  attributes={this.props.attributes}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
}
