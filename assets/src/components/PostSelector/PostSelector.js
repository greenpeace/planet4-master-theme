import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
const {apiFetch} = wp;
const {addQueryArgs} = wp.url;
import {
  FormTokenField,
} from '@wordpress/components';

class PostSelector extends Component {
  constructor( props ) {
    super( props );

    this.handleChange = this.handleChange.bind( this );
    this.getValue = this.getValue.bind( this );
    this.state = {
      posts: [],
    };

    let args = {};
    // If WPML is active then this variable contains the post language.
    if ( typeof icl_this_lang !== 'undefined' ) {
      args = { post_language: icl_this_lang};
    }

    if ( props.postType === 'post' ) {
      apiFetch(
        {
          path: addQueryArgs('/planet4/v1/all-published-posts', args)
        }
      ).then( posts => {
        this.setState( { posts: posts } );
      });
    } else if ( props.postType === 'act_page' ) {
      let queryArgs;

      queryArgs = {
        path: addQueryArgs('/wp/v2/pages', {
          per_page: -1,
          post_type: 'page',
          post_parent: window.p4ge_vars.planet4_options.act_page,
          orderby: 'title',
          post_status: 'publish',

        })
      };


      apiFetch( queryArgs )
        .then( posts => {
          this.setState( {
              posts: posts.map( post => ({
                post_title: post.title.rendered,
                id: post.id,
              }) )
            }
          );
        } );
    }
  }

  handleChange( value ) {
    const postIds = value.map( token => this.state.posts.find( post => post.post_title === token ).id );
    this.props.onChange( postIds );
  }

  getValue() {
    const { value } = this.props;
    if ( !value ) {
      return null;
    }
    const posts = value.reduce( ( accumulator, postId ) => {
      const post = this.state.posts.find( post => Number( post.id ) === Number( postId ) );
      if ( post ) {
        accumulator.push( post );
      }
      return accumulator;
    }, [] );

    return posts.map( post => post.post_title );
  }

  render() {
    const { onChange, label, placeholder, value, postType, ...ownProps } = this.props;

    if ( this.state.posts.length === 0 ) {
      return null;
    }

    return <FormTokenField
      suggestions={ this.state.posts.map( post => post.post_title ) }
      label={ label || 'Select Posts' }
      onChange={ this.handleChange }
      placeholder={placeholder || 'Select Posts'}
      value={this.getValue()}
      { ...ownProps }
    />;
  }
}

export default compose(
)( PostSelector );
