import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect } from "@wordpress/data";
import {
  FormTokenField,
} from '@wordpress/components';

class PostTypeSelector extends Component {
  constructor( props ) {
    super( props );

    this.handleChange = this.handleChange.bind( this );
  }

  handleChange( value ) {
    const postTypeIds = value.map( postTypeName => {
      return this.props.postTypes.find( postType => postType.name === postTypeName ).id;
    });
    this.props.onChange( postTypeIds );
  }

  render() {
    const { postTypes, onChange, label, placeholder, value, ...ownProps } = this.props;

    if ( !postTypes || postTypes.length === 0 ) {
      return null;
    }

    return <FormTokenField
      suggestions={ postTypes.map( postType => postType.name ) }
      label={ label || 'Select Post Types' }
      onChange={ this.handleChange }
      placeholder={placeholder || 'Select Post Types'}
      value={ value ? value.map( postTypeId => postTypes.find( postType => Number(postType.id) === Number(postTypeId) ).name ) : [] }
      { ...ownProps }
    />;
  }
}

export default compose(
  withSelect( select => ({
    postTypes: select( 'core' ).getEntityRecords( 'taxonomy', 'p4-page-type' )
  }) )
)( PostTypeSelector );
