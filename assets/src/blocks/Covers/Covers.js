import {Component, Fragment} from '@wordpress/element';

import {
  FormTokenField,
  SelectControl,
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender } from '@wordpress/components';

import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';

const {apiFetch} = wp;
const {addQueryArgs} = wp.url;

const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

const TYPE_TAKE_ACTION = '1';
const TYPE_CAMPAIGN = '2';
const TYPE_CONTENT = '3';

export class Covers extends Component {
    constructor(props) {
      super(props);

      // Populate tag tokens for saved tags.
      let tagTokens = props.tagsList.filter(tag => props.tags.includes(tag.id)).map(tag => tag.name);
      // Populate post types tokens for saved post types.
      let postTypeTokens = props.postTypesList.filter(post_type => props.post_types.includes(post_type.id)).map(post_type => post_type.name);

      const { __ } = wp.i18n;

      this.options = [{
          label: __('Content Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/content_covers.png',
          value: TYPE_CONTENT,
          help: __('Content covers pull the image from the post.')
        }];

      // Get current post type to filter LayoutSelector options in the case of "campaigns" post type.
      let currentPostType = wp.data.select('core/editor').getCurrentPostType();

      if ('campaign' !== currentPostType) {
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

      // Populate component state with block's saved tags tokens and post type tokens
      this.state = {
        tagTokens: tagTokens,
        postTypeTokens: postTypeTokens,
        selectedPosts: [],
      };

      this.populatePostsToken();
      this.searchTimeout = null;
    }

    static getDerivedStateFromProps(props, state) {

      // Post types should be available for cover_type 3
      // If cover_type is not 3, reset post types tokens.
      if ( [TYPE_TAKE_ACTION, TYPE_CAMPAIGN].includes( props.cover_type ) ) {
        state.postTypeTokens= [];
      }

      // If posts attribute was reset, reset also the posts tokens.
      if (0 === props.posts.length) {
        state.postsTokens= [];
      }
      return state;
    }


    /**
     * Set component's state for existing blocks.
     */
    populatePostsToken() {

      if (this.props.posts.length > 0) {

        let post_type = this.props.cover_type === TYPE_TAKE_ACTION ? 'pages' : 'posts';
        apiFetch(
          {
            path: addQueryArgs('/wp/v2/'+ post_type, {
              per_page: 50,
              page: 1,
              include: this.props.posts
            })
          }
        ).then(posts => {
          const postsTokens = posts.map(post => post.title.rendered);
          const postsSuggestions = posts.map(post => post.title.rendered);
          const postsList = posts.map( post => ({
            id: post.id,
            title: post.title.rendered,
          }) );

          this.setState({
              postsTokens: postsTokens,
              postsList: posts,
              postsSuggestions: postsSuggestions,
              selectedPosts: posts,
            }
          );
        });
      } else {
        this.setState(
          {
            postsTokens: [],
            postsList: [],
            postsSuggestions: [],
            selectedPosts: [],
          }
        );
      }
    }

    /**
     * Search posts using wp api.
     *
     * @param tokens
     */
    searchPosts(tokens) {

      let queryArgs;
      if (TYPE_TAKE_ACTION === this.props.cover_type) {

        queryArgs = {
          path: addQueryArgs('/wp/v2/pages', {
            per_page: -1,
            post_type: 'page',
            post_parent: window.p4ge_vars.planet4_options.act_page,
            search: tokens,
            orderby: 'title',
            post_status: 'publish',

          })
        };
      } else {

        queryArgs = {
          path: addQueryArgs('/wp/v2/posts', {
            per_page: 50,
            page: 1,
            search: tokens,
            orderby: 'title',
            post_status: 'publish',

          })
        };
      }


      apiFetch(queryArgs)
        .then(posts => {
          let postsSuggestions = posts.map(post => post.title.rendered);
          this.setState({postsSuggestions: postsSuggestions, postsList: posts})
        });
    }

    onPostsSearch(token) {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(function () {
        this.searchPosts(token);
      }.bind(this), 500);
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

    onTagsChange(tokens) {
      const tagIds = tokens.map( token => {
        return this.props.tagsList.filter( tag => tag.name === token )[0].id;
      });
      this.props.setAttributes( { tags: tagIds } );
      this.setState({ tagTokens: tokens })
    }

    onPostTypesChange(tokens) {
      const postTypeIds = tokens.map( token => {
        return this.props.postTypesList.filter( postType => postType.name === token )[0].id;
      });
      this.props.setAttributes( { post_types: postTypeIds } );
      this.setState({ postTypeTokens: tokens })
    }

    onPostsChange(tokens) {
      // Array to hold references to selected posts objects.
      let currentSelectedPosts = [];
      tokens.forEach(token => {
        let f = this.state.postsList.filter(post => post.title.rendered === token);
        if (f.length > 0) {
          currentSelectedPosts.push(f[0]);
        }
        f = this.state.selectedPosts.filter(post => post.title.rendered === token);
        if (f.length > 0) {
          currentSelectedPosts.push(f[0]);
        }
      });

      const postIds = currentSelectedPosts.map(post => post.id);
      this.props.setAttributes({posts: postIds});
      this.setState({postsTokens: tokens, selectedPosts: currentSelectedPosts});
    }

    renderEdit() {
      const { __ } = wp.i18n;

      const tagSuggestions = this.props.tagsList.map( tag => tag.name );
      const postTypeSuggestions = this.props.postTypesList.map( postType => postType.name );

      const toAttribute = attributeName => value => {
        this.props.setAttributes( { [ attributeName ]: value } );
      };

      return (
        <div>
          <h3>{ __('What style of cover do you need?', 'p4ge') }</h3>

          <div>
            <LayoutSelector
              selectedOption={ this.props.cover_type }
              onSelectedLayoutChange={ this.onLayoutChange }
              options={this.options}
            />
          </div>

          <div>
            <SelectControl
              label="Rows to display"
              value={ this.props.covers_view }
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
              value={ this.props.title }
              onChange={ toAttribute('title')}
              characterLimit={40}
            />
          </div>

          <div>
            <TextareaControl
              label="Description"
              placeholder="Enter description"
              value={ this.props.description }
              onChange={ toAttribute('description')}
              characterLimit={200}
            />
          </div>

          {
            this.props.posts !== 'undefined' && this.props.posts.length === 0
              ?
              <div>

                <FormTokenField
                  value={this.state.tagTokens}
                  suggestions={tagSuggestions}
                  label='Select Tags'
                  onChange={this.onTagsChange}
                  placeholder="Select Tags"
                />
                <p class='FieldHelp'>Associate this block with Actions that have specific Tags</p>
              </div>
              : null
          }

          {
            this.props.cover_type === TYPE_CONTENT && this.props.posts.length === 0
              ? <FormTokenField
                value={this.state.postTypeTokens}
                suggestions={postTypeSuggestions}
                label='Post Types'
                onChange={this.onPostTypesChange}
                placeholder="Select Tags"
              />
              : null
          }

          {
            ([TYPE_TAKE_ACTION, TYPE_CONTENT].includes(this.props.cover_type)) &&
            (this.props.tags.length === 0 && this.props.post_types.length === 0)
              ? <div>
                <label>Manual override</label>
                <FormTokenField
                  value={this.state.postsTokens}
                  suggestions={this.state.postsSuggestions}
                  label='CAUTION: Adding covers manually will override the automatic functionality.'
                  onChange={this.onPostsChange}
                  onInputChange={this.onPostsSearch}
                  placeholder="Select Tags"
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
