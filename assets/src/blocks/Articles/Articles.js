import {React, Component, Fragment} from 'react';
import {Preview} from '../../components/Preview';
import {
  FormTokenField,
  CheckboxControl,
  TextControl,
  TextareaControl,
  ServerSideRender
} from '@wordpress/components';


const {apiFetch} = wp;
const {addQueryArgs} = wp.url;

export class Articles extends Component {
  constructor(props) {
    super(props);

    // Populate tag tokens for saved tags.
    let tagTokens = props.tagsList.filter(tag => props.tags.includes(tag.id)).map(tag => tag.name);
    // Populate post types tokens for saved post types.
    let postTypeTokens = props.postTypesList.filter(post_type => props.post_types.includes(post_type.id)).map(post_type => post_type.name);

    this.state = {
      tagTokens: tagTokens,
      postTypeTokens: postTypeTokens,
      selectedPosts: [],
    };

    this.populatePostsToken();
  }

  /**
   * Set component's state for existing blocks.
   */
  populatePostsToken() {

    if (this.props.posts.length > 0) {

      apiFetch(
        {
          path: addQueryArgs('/wp/v2/posts', {
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
  onPostsSearch(tokens) {

    apiFetch(
      {
        path: addQueryArgs('/wp/v2/posts', {
          per_page: 50,
          page: 1,
          search: tokens,
          orderby: 'title',
          post_status: 'publish',

        })
      }
    ).then(posts => {
      let postsSuggestions = posts.map(post => post.title.rendered);
      this.setState({postsSuggestions: postsSuggestions, postsList: posts})
    });
  }

  onSelectedTagsChange(tokens) {
    const tagIds = tokens.map(token => {
      return this.props.tagsList.filter(tag => tag.name === token)[0].id;
    });
    this.props.onSelectedTagsChange(tagIds);
    this.setState({tagTokens: tokens})
  }

  onSelectedPostTypesChange(tokens) {
    const postTypeIds = tokens.map(token => {
      return this.props.postTypesList.filter(postType => postType.name === token)[0].id;
    });
    this.props.onSelectedPostTypesChange(postTypeIds);
    this.setState({postTypeTokens: tokens})
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
    const {__} = wp.i18n;

    const tagSuggestions = this.props.tagsList.map(tag => tag.name);
    const postTypeSuggestions = this.props.postTypesList.map(postType => postType.name);

    return (
      <Fragment>
        <div>
          <TextControl
            label={__('Title', 'p4ge')}
            placeholder={__('Enter title', 'p4ge')}
            help={__('Your default is set to [ Latest Articles ]', 'p4ge')}
            value={this.props.article_heading}
            onChange={this.props.onTitleChange}
          />
        </div>


        <div>
          <TextareaControl
            label={__('Description', 'p4ge')}
            placeholder={__('Enter description', 'p4ge')}
            value={this.props.articles_description}
            onChange={this.props.onDescriptionChange}
          />
        </div>

        <div>
          <TextControl
            label={__('Button Text', 'p4ge')}
            placeholder={__('Override button text', 'p4ge')}
            help={__('Your default is set to [ Load More ]', 'p4ge')}
            value={this.props.read_more_text}
            onChange={this.props.onReadmoretextChange}
          />
        </div>


        <div>
          <TextControl
            label={__('Button Link', 'p4ge')}
            placeholder={__('Add read more button link', 'p4ge')}
            value={this.props.read_more_link}
            onChange={this.props.onReadmorelinkChange}
          />
        </div>

        <div>
          <CheckboxControl
            label={__('Open in a new Tab', 'p4ge')}
            help={__('Open button link in new tab', 'p4ge')}
            value={this.props.button_link_new_tab}
            checked={this.props.button_link_new_tab}
            onChange={(e) => this.props.onButtonLinkTabChange(e)}
          />
        </div>

        {
          this.props.posts !== 'undefined' && this.props.posts.length === 0
            ?
            <Fragment>
              <div>
                <TextControl
                  label={__('Articles count', 'p4ge')}
                  help={__('Number of articles', 'p4ge')}
                  type="number"
                  value={this.props.article_count}
                  onChange={this.props.onCountChange}
                />
              </div>
              <div>
                <FormTokenField
                  value={this.state.tagTokens}
                  suggestions={tagSuggestions}
                  label={__('Select Tags', 'p4ge')}
                  onChange={tokens => this.onSelectedTagsChange(tokens)}
                />
                <p className='FieldHelp'>Associate this block with Actions that have specific Tags</p>
              </div>
              <div>
                <FormTokenField
                  value={this.state.postTypeTokens}
                  suggestions={postTypeSuggestions}
                  label={__('Post Types', 'p4ge')}
                  onChange={tokens => this.onSelectedPostTypesChange(tokens)}
                />
              </div>
              <div className="ignore-categories-wrapper">
                <CheckboxControl
                  label={__('Ignore categories', 'p4ge')}
                  help={__('Ignore categories when filtering posts to populate the content of this block', 'p4ge')}
                  value={this.props.ignore_categories}
                  checked={this.props.ignore_categories}
                  onChange={(e) => this.props.onIgnoreCategoriesChange(e)}
                />
              </div>
            </Fragment>
            : null
        }

        {
          (this.props.tags.length === 0 && this.props.post_types.length === 0)
            ? <div>
              <hr/>
              <label>{__('Manual override', 'p4ge')}</label>
              <FormTokenField
                value={this.state.postsTokens}
                suggestions={this.state.postsSuggestions}
                label={__('CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains in tact.', 'p4ge')}
                onChange={tokens => this.onSelectedPostsChange(tokens)}
                onInputChange={tokens => this.onPostsSearch(tokens)}
                placeholder="Select Posts"
                maxLength="10"
                maxSuggestions="20"
              />
            </div>
            : null
        }

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
            block={'planet4-blocks/articles'}
            attributes={{
              read_more_text: this.props.read_more_text,
              read_more_link: this.props.read_more_link,
              tags: this.props.tags,
              post_types: this.props.post_types,
              posts: this.props.posts,
              article_heading: this.props.article_heading,
              articles_description: this.props.articles_description,
              article_count: this.props.article_count,
              ignore_categories: this.props.ignore_categories,
            }}>
          </ServerSideRender>
        </Preview>
      </div>
    );
  }
}
