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

const getValueFromProps = ( props ) => {
  const { metaKey, postMeta, defaultValue } = props;

  const metaValue = postMeta[ metaKey ];

  const shouldUseDefault = defaultValue
    && (
      !metaValue
      || !props.options.some( option => option.value === metaValue )
    );
  return shouldUseDefault ? defaultValue : metaValue;

}

export function withPostMeta( WrappedComponent ) {

  class WrappingComponent extends Component {
    constructor( props ) {
      super( props );
      this.handleChange = this.handleChange.bind( this );
      this.valuePropName = getValuePropName( WrappedComponent );
    }

    async handleChange( metaKey, value ) {
      const getNewMeta = this.props.getNewMeta || (( metaKey, value, meta ) => {
        return { [ metaKey ]: value };
      });
      const meta = await getNewMeta( metaKey, value, this.props.postMeta );
      this.props.writeMeta( meta );
    }

    render() {
      const { metaKey, postMeta, writeMeta, getNewMeta, onChange, defaultValue, ...ownProps } = this.props;
      const value = getValueFromProps( this.props );

      return <WrappedComponent
        { ...{
          [ this.valuePropName ]: value,
          onChange: ( value ) => {
            this.handleChange( metaKey, value || '' );
            // Fire any onchange event if passed by wrapped component.
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
          writeMeta: ( meta ) => {
            dispatch( 'core/editor' ).editPost( { meta } );
          }
        };
      }
    )
  )( WrappingComponent );
}
