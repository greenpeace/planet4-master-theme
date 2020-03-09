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
  }

  handleChange( value ) {
    const tagIds = value.map( token => {
      return this.props.tagSuggestions.find( tag => tag.name === token ).id;
    });
    this.props.onChange( tagIds );
  }

  render() {
    const { tagSuggestions, onChange, label, placeholder, value, ...ownProps } = this.props;

    return <FormTokenField
      suggestions={ tagSuggestions.map( tagSuggestion => tagSuggestion.name ) }
      label={ label || 'Select Tags' }
      onChange={ this.handleChange }
      placeholder={placeholder || 'Select Tags'}
      value={ value ? value.map( tagId => tagSuggestions.find( tag => tag.id === tagId ).name ) : [] }
      { ...ownProps }
    />;
  }
}

export default compose(
  withSelect( select => ({
    tagSuggestions: select( 'core' ).getEntityRecords( 'taxonomy', 'post_tag', { hide_empty: false, per_page: -1 } )
  }) )
)( TagSelector );
