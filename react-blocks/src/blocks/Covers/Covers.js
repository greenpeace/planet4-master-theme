import { React, Component } from 'react';

import {
  FormTokenField,
  SelectControl,
  TextControl,
  TextareaControl,
  ServerSideRender } from '@wordpress/components';

import { LayoutSelector } from '../../components/LayoutSelector/LayoutSelector';
import { Preview } from '../../components/Preview';

export class Covers extends Component {
    constructor(props) {
      super(props);
      this.state = {
        tagTokens: [],
        postTypeTokens: []
      };
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

    renderEdit() {
      const { __ } = wp.i18n;

      const tagSuggestions = this.props.tagsList.map( tag => tag.name );
      const postTypeSuggestions = this.props.postTypesList.map( postType => postType.name );
      const postsSuggestions = this.props.posts.map( post => post.title.rendered );

      return (
        <div>
          <h3>{ __('What style of cover do you need?', 'p4ge') }</h3>

          <div>
            <LayoutSelector
              selectedOption={ this.props.cover_type }
              onSelectedLayoutChange={ this.props.onSelectedLayoutChange }
              options={[
                {
                  label: __('Take Action Covers', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/take_action_covers.png',
                  value: 1,
                  help: __('Take action covers pull the featured image, tags, have a 25 character excerpt and have a call to action button')
                },
                {
                  label: __('Campaign Covers', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/campaign_covers.png',
                  value: 2,
                  help: __('Campaign covers pull the associated image and hashtag from the system tag definitions.'),
                },
                {
                  label: __('Content Covers', 'p4ge'),
                  image: window.p4ge_vars.home + 'images/content_covers.png',
                  value: 3,
                  help: __('Content covers pull the image from the post.')
                },
              ]}
            />
          </div>

          <div>
            <SelectControl
              label="Rows to display"
              value={ this.props.covers_view }
              options={ [
                { label: '1 Row', value: '1' },
                { label: '2 Rows', value: '2' },
                { label: 'All rows', value: 'all' },
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

          <div>
            <FormTokenField
              value={ this.state.tagTokens }
              suggestions={ tagSuggestions }
              label='Select Tags'
              onChange={ tokens => this.onSelectedTagsChange(tokens) }
              placeholder="Select Tags"
            />
            <p class='FieldHelp'>Associate this block with Actions that have specific Tags</p>
          </div>

          {
            this.props.cover_type === 3
            ? <FormTokenField
              value={ this.state.postTypeTokens }
              suggestions={ postTypeSuggestions }
              label='Post Types'
              onChange={ tokens => this.onSelectedPostTypesChange(tokens) }
              placeholder="Select Tags"
            />
            : null
          }

          {
            this.props.cover_type === 3 &&
            (this.props.tags.length === 0 || this.props.post_types.length === 0)
            ? <div>
                <label>Manual override</label>
                <FormTokenField
                  value={ this.props.selectedPosts }
                  suggestions={ postsSuggestions }
                  label='CAUTION: Adding covers manually will override the automatic functionality.
                  DRAG & DROP: Drag and drop to reorder cover display priority.'
                  onChange={ tokens => this.props.onSelectedPostsChange(tokens) }
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
                    title: this.props.title,
                    description: this.props.description,
                  }}>
                </ServerSideRender>
              </Preview>
          </div>
      );
    }
};
