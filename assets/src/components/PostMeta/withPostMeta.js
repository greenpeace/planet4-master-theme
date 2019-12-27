import { withSelect, withDispatch } from "@wordpress/data";
import { RadioControl } from "@wordpress/components";
import { compose } from '@wordpress/compose';
import { Component } from '@wordpress/element';

const getValuePropName = ( Component ) => {
  switch ( Component ) {
    case RadioControl:
      return 'selected';
  }

  return 'value';
};

export function withPostMeta( WrappedComponent ) {

  class WrappingComponent extends Component {
    constructor( props ) {
      super( props );
      this.handleChange = this.handleChange.bind( this );
      this.valuePropName = getValuePropName( WrappedComponent );
    }

    handleChange( metaKey, value ) {
      this.props.writeMeta( metaKey, value );
    }

    render() {
      const { metaKey, postMeta, writeMeta, onChange, ...ownProps } = this.props;

      return <WrappedComponent
        { ...{
          [ this.valuePropName ]: postMeta[ metaKey ],
          onChange: ( value ) => {
            this.handleChange( metaKey, value || '' );
            if ( onChange ) {
              onChange( value );
            }
          }
        } }
        { ...ownProps }
      />;
    }
  }

  return compose(
    withSelect(
      ( select ) => {
        return {
          postMeta: select( 'core/editor' ).getEditedPostAttribute( 'meta' )
        };
      }
    ),
    withDispatch(
      ( dispatch ) => {
        return {
          writeMeta: ( metaKey, value ) => {
            dispatch( 'core/editor' ).editPost( { meta: { [ metaKey ]: value } } );
          }
        };
      }
    )
  )( WrappingComponent );
}
