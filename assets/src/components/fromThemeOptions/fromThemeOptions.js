import { Component } from '@wordpress/element';

const getFieldFromTheme = ( theme, fieldName ) => {
  if ( !theme ) {
    return null;
  }
  return theme.fields.find( field => field.id === fieldName );
};

export function fromThemeOptions( WrappedComponent ) {

  return class extends Component {
    render() {
      const { theme, ...ownProps } = this.props;

      if ( !theme ) {
        return <WrappedComponent { ...ownProps }/>
      }

      const field = getFieldFromTheme( theme, ownProps.metaKey );

      if ( !field || !field.options ) {
        return null;
      }

      return <WrappedComponent defaultValue={ field.default } options={ field.options } { ...ownProps } />;
    }
  };
}
