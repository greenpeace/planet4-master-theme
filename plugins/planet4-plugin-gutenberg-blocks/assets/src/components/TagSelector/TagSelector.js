import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect } from "@wordpress/data";
import {
  FormTokenField,
} from '@wordpress/components';

class TagSelector extends Component {
  constructor( props ) {
    super( props );

    this.handleChange = this.handleChange.bind( this );
    this.getValue = this.getValue.bind( this );
  }

  handleChange( value ) {
    const tagIds = value.map( token => {
      return this.props.tagSuggestions.find( tag => tag.name === token ).id;
    });
    this.props.onChange( tagIds );
  }

  getValue() {
    const { tagSuggestions, value } = this.props;

    if ( !tagSuggestions || !value ) {
      return null;
    }

    const tags = value.reduce( ( accumulator, tagId ) => {
      const tag = tagSuggestions.find( tag => Number( tag.id ) === Number( tagId ) );
      if ( tag ) {
        accumulator.push( tag );
      }
      return accumulator;
    }, [] );

    return tags.map( tag => tag.name );
  }

  render() {
    const { tagSuggestions, onChange, label, placeholder, value, ...ownProps } = this.props;

    if ( !tagSuggestions || tagSuggestions.length === 0 ) {
      return null;
    }

    return <FormTokenField
      suggestions={ tagSuggestions.map( tagSuggestion => tagSuggestion.name ) }
      label={ label || 'Select Tags' }
      onChange={ this.handleChange }
      placeholder={placeholder || 'Select Tags'}
      value={this.getValue()}
      { ...ownProps }
    />;
  }
}

export default compose(
  withSelect( select => ({
    tagSuggestions: select( 'core' ).getEntityRecords( 'taxonomy', 'post_tag', { hide_empty: false, per_page: -1 } )
  }) )
)( TagSelector );
