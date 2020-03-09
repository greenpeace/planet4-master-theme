import {Component, Fragment} from '@wordpress/element';
import {Preview} from '../../components/Preview';
import {
  FormTokenField,
  CheckboxControl,
  TextControl as BaseTextControl,
  TextareaControl as BaseTextareaControl,
  ServerSideRender
} from '@wordpress/components';
import withCharacterCounter from '../../components/withCharacterCounter/withCharacterCounter';


const {apiFetch} = wp;
const {addQueryArgs} = wp.url;
const TextControl = withCharacterCounter( BaseTextControl );
const TextareaControl = withCharacterCounter( BaseTextareaControl );

export class Articles extends Component {
  constructor(props) {
    super(props);

    // Populate tag tokens for saved tags.
    let tagTokens = props.tagsList.filter( tag => props.attributes.tags.includes( tag.id ) ).map( tag => tag.name );
    // Populate post types tokens for saved post types.
    let postTypeTokens = props.postTypesList.filter( post_type => props.attributes.post_types.includes( post_type.id ) ).map( post_type => post_type.name );

    this.state = {
      tagTokens: tagTokens,
      postTypeTokens: postTypeTokens,
      selectedPosts: [],
      postsSuggestions: null,
      overrideWasFocused: false,
    };
    this.onSelectedTagsChange = this.onSelectedTagsChange.bind( this );
    this.onSelectedPostTypesChange = this.onSelectedPostTypesChange.bind( this );
    this.getSuggestionsOnFirstFocus = this.getSuggestionsOnFirstFocus.bind( this );
    this.onSelectedPostsChange = this.onSelectedPostsChange.bind( this );
  }

  componentDidMount() {
    this.populatePostsToken();
  }

  /**
   * Set component's state for existing blocks.
   */
  populatePostsToken() {

    if (this.props.attributes.posts.length > 0) {

      apiFetch(
        {
          path: addQueryArgs( '/wp/v2/posts', {
            per_page: 50,
            page: 1,
            include: this.props.attributes.posts
          } )
        }
      ).then( posts => {
        const postsTokens = posts.map( post => post.title.rendered );
        const postsList = posts.map( post => ({
          id: post.id,
          title: post.title.rendered,
        }) );

        this.setState({
            postsTokens: postsTokens,
            postsList: postsList,
            postsSuggestions: [],
            selectedPosts: postsList,
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
   * @param event
   */
  getSuggestionsOnFirstFocus(event) {
    // Fetch the suggestions the first time input changes
    if ( this.state.overrideWasFocused ) {
      return;
    }
    this.setState( { overrideWasFocused: true } );

    let args;
    // If WPML is active then this variable contains the post language.
    if ( typeof icl_this_lang !== 'undefined' ) {
      args = { post_language: icl_this_lang};
    } else {
      args = {};
    }

    apiFetch(
      {
        path: addQueryArgs('/planet4/v1/all-published-posts', args)
      }
    ).then(posts => {
      let postsSuggestions = posts.map(post => post.post_title);
      const postsList = posts.map( post => ({
        id: post.id,
        title: post.post_title,
      }) );
      this.setState({postsSuggestions: postsSuggestions, postsList: postsList})
    });
  }

  onSelectedTagsChange(tokens) {
    const tagIds = tokens.map(token => {
      return this.props.tagsList.filter(tag => tag.name === token)[0].id;
    });
    this.props.setAttributes({tags: tagIds});
    this.setState({tagTokens: tokens})
  }

  onSelectedPostTypesChange(tokens) {
    const postTypeIds = tokens.map(token => {
      return this.props.postTypesList.filter(postType => postType.name === token)[0].id;
    });
    this.props.setAttributes({post_types: postTypeIds});

    this.setState({postTypeTokens: tokens})
  }

  onSelectedPostsChange(tokens) {
    const selectedPosts = tokens.map( token => this.state.postsList.find( post => post.title === token ) );

    this.props.setAttributes({posts: selectedPosts.map( post => post.id )});

    this.setState({postsTokens: tokens, selectedPosts: selectedPosts});
  }

  renderEdit() {
    const {__} = wp.i18n;

    const tagSuggestions = this.props.tagsList.map(tag => tag.name);
    const postTypeSuggestions = this.props.postTypesList.map(postType => postType.name);

    const toAttribute = attributeName => value => {
      this.props.setAttributes( { [ attributeName ]: value } );
    };

    return (
      <Fragment>
        <div>
          <TextControl
            label={__('Title', 'p4ge')}
            placeholder={__('Enter title', 'p4ge')}
            help={__('Your default is set to [ Latest Articles ]', 'p4ge')}
            value={this.props.attributes.article_heading}
            onChange={ toAttribute('article_heading')}
            characterLimit={40}
          />
        </div>


        <div>
          <TextareaControl
            label={__('Description', 'p4ge')}
            placeholder={__('Enter description', 'p4ge')}
            value={this.props.attributes.articles_description}
            onChange={ toAttribute('articles_description')}
            characterLimit={200}
          />
        </div>

        <div>
          <TextControl
            label={__('Button Text', 'p4ge')}
            placeholder={__('Override button text', 'p4ge')}
            help={__('Your default is set to [ Load More ]', 'p4ge')}
            value={this.props.attributes.read_more_text}
            onChange={ toAttribute('read_more_text')}
          />
        </div>


        <div>
          <TextControl
            label={__('Button Link', 'p4ge')}
            placeholder={__('Add read more button link', 'p4ge')}
            value={this.props.attributes.read_more_link}
            onChange={ toAttribute('read_more_link')}
          />
        </div>

        <div>
          <CheckboxControl
            label={__('Open in a new Tab', 'p4ge')}
            help={__('Open button link in new tab', 'p4ge')}
            value={this.props.button_link_new_tab}
            checked={this.props.button_link_new_tab}
            onChange={ toAttribute('button_link_new_tab')}
          />
        </div>

        {
          this.props.attributes.posts !== 'undefined' && this.props.attributes.posts.length === 0
            ?
            <Fragment>
              <div>
                <TextControl
                  label={__('Articles count', 'p4ge')}
                  help={__('Number of articles', 'p4ge')}
                  type="number"
                  value={this.props.attributes.article_count}
                  onChange={toAttribute('article_count')}
                />
              </div>
              <div>
                <FormTokenField
                  suggestions={tagSuggestions}
                  label={__('Select Tags', 'p4ge')}
                  value={this.state.tagTokens}
                  onChange={this.onSelectedTagsChange}
                />
                <p className='FieldHelp'>Associate this block with Actions that have specific Tags</p>
              </div>
              <div>
                <FormTokenField
                  suggestions={postTypeSuggestions}
                  label={__('Post Types', 'p4ge')}
                  value={this.state.postTypeTokens}
                  onChange={this.onSelectedPostTypesChange}
                />
              </div>
              <div className="ignore-categories-wrapper">
                <CheckboxControl
                  label={__('Ignore categories', 'p4ge')}
                  help={__('Ignore categories when filtering posts to populate the content of this block', 'p4ge')}
                  value={this.props.attributes.ignore_categories}
                  checked={this.props.attributes.ignore_categories}
                  onChange={ toAttribute('ignore_categories')}
                />
              </div>
            </Fragment>
            : null
        }

        {
          (this.props.attributes.tags.length === 0 && this.props.attributes.post_types.length === 0) &&
          <div>
            <hr/>
            <label>{ __( 'Manual override', 'p4ge' ) }</label>
            <FormTokenField
              value={ this.state.postsTokens }
              suggestions={ this.state.postsSuggestions }
              label={ __( 'CAUTION: Adding articles individually will override the automatic functionality of this block. For good user experience, please include at least three articles so that spacing and alignment of the design remains in tact.', 'p4ge' ) }
              onFocus={ this.getSuggestionsOnFirstFocus }
              onChange={ this.onSelectedPostsChange }
              placeholder="Select Posts"
              maxLength="10"
              maxSuggestions="20"
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
