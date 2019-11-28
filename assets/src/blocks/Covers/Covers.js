import React, { Component } from 'react';

import {
  FormTokenField,
  SelectControl,
  TextControl,
  TextareaControl,
  ServerSideRender } from '@wordpress/components';

import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';

const {apiFetch} = wp;
const {addQueryArgs} = wp.url;

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
          value: '3',
          help: __('Content covers pull the image from the post.')
        }];

      // Get current post type to filter LayoutSelector options in the case of "campaigns" post type.
      let currentPostType = wp.data.select('core/editor').getCurrentPostType();

      if ('campaign' !== currentPostType) {
        this.options.push({
          label: __('Take Action Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/take_action_covers.png',
          value: '1',
          help: __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button')
        });
        this.options.push({
          label: __('Campaign Covers', 'p4ge'),
          image: window.p4ge_vars.home + 'images/campaign_covers.png',
          value: '2',
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
      if ('1' === props.cover_type || '2' === props.cover_type) {
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

        let post_type = this.props.cover_type === '1' ? 'pages' : 'posts';
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
      if ('1' === this.props.cover_type) {

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

    onSelectedTagsChange(tokens) {
      const tagIds = tokens.map( token => {
        return this.props.tagsList.filter( tag => tag.name === token )[0].id;
      });
      this.props.onSelectedTagsChange(tagIds);
      this.setState({ tagTokens: tokens })
    }

    onSelectedPostTypesChange(tokens) {
      const postTypeIds = tokens.map( token => {
        return this.props.postTypesList.filter( postType => postType.name === token )[0].id;
      });
      this.props.onSelectedPostTypesChange(postTypeIds);
      this.setState({ postTypeTokens: tokens })
    }

    onSelectedPostsChange(tokens) {
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
      this.props.onSelectedPostsChange(postIds);
      this.setState({postsTokens: tokens, selectedPosts: currentSelectedPosts});
    }

    renderEdit() {
      const { __ } = wp.i18n;

      const tagSuggestions = this.props.tagsList.map( tag => tag.name );
      const postTypeSuggestions = this.props.postTypesList.map( postType => postType.name );

      return (
        <div>
          <h3>{ __('What style of cover do you need?', 'p4ge') }</h3>

          <div>
            <LayoutSelector
              selectedOption={ this.props.cover_type }
              onSelectedLayoutChange={ this.props.onSelectedLayoutChange }
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
              onChange={ this.props.onRowsChange }
            />
          </div>

          <div>
            <TextControl
              label="Title"
              placeholder="Enter title"
              value={ this.props.title }
              onChange={ this.props.onTitleChange }
            />
          </div>

          <div>
            <TextareaControl
              label="Description"
              placeholder="Enter description"
              value={ this.props.description }
              onChange={ this.props.onDescriptionChange }
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
                  onChange={tokens => this.onSelectedTagsChange(tokens)}
                  placeholder="Select Tags"
                />
                <p class='FieldHelp'>Associate this block with Actions that have specific Tags</p>
              </div>
              : null
          }

          {
            this.props.cover_type === '3' && this.props.posts.length === 0
              ? <FormTokenField
                value={this.state.postTypeTokens}
                suggestions={postTypeSuggestions}
                label='Post Types'
                onChange={tokens => this.onSelectedPostTypesChange(tokens)}
                placeholder="Select Tags"
              />
              : null
          }

          {
            (this.props.cover_type === '1' || this.props.cover_type === '3') &&
            (this.props.tags.length === 0 && this.props.post_types.length === 0)
              ? <div>
                <label>Manual override</label>
                <FormTokenField
                  value={this.state.postsTokens}
                  suggestions={this.state.postsSuggestions}
                  label='CAUTION: Adding covers manually will override the automatic functionality.'
                  onChange={tokens => this.onSelectedPostsChange(tokens)}
                  onInputChange={token => this.onPostsSearch(token)}
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
                  attributes={{
                    cover_type: this.props.cover_type,
                    covers_view: this.props.covers_view,
                    tags: this.props.tags,
                    post_types: this.props.post_types,
                    posts: this.props.posts,
                    title: this.props.title,
                    description: this.props.description,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
};
