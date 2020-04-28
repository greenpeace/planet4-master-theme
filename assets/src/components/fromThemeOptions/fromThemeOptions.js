import { Component } from '@wordpress/element';

export const getFieldFromTheme = ( theme, fieldName, meta ) => {
  if ( !theme ) {
    return null;
  }

  const field = theme.fields.find( field => field.id === fieldName );

  if ( !field ) {
    return null;
  }

  if ( field.dependsOn ) {
    return resolveDependency( theme, field, meta );
  }

  return field;
};

const resolveDependency = ( theme, field, meta ) => {
  const dependencyField = theme.fields.find( field2 => field2.id === field.dependsOn );

  if ( !dependencyField ) {
    return null;
  }

  if ( !meta ) {
    return null;
  }

  const dependencyConfiguration = dependencyField.dependsOn ? resolveDependency( theme, dependencyField, meta ) : dependencyField;

  if ( !dependencyConfiguration ) {
    return null;
  }

  let dependencyValue = meta[ field.dependsOn ];

  // The field has a dependency and the current value of the post is not in the list of options, so use default value of the dependency.
  if ( dependencyConfiguration ) {
    const dependencyValueIsAllowed = dependencyConfiguration.options && dependencyConfiguration.options.find( option => option.value === dependencyValue );
    if ( !dependencyValueIsAllowed ) {
      dependencyValue = dependencyConfiguration.default;
    }
  }


  if ( !dependencyValue ) {
    return null;
  }

  return {
    id: field.id,
    ...field.configurations[ dependencyValue ],
  };
};

const getDependencyUpdates = ( theme, fieldName, value, meta ) => {
  const allChildren = theme.fields.filter( field => field.dependsOn === fieldName );
  const needUpdate = allChildren.filter(
    field => {
      const configuration = field.configurations[ value ];

      if ( !configuration ) {
        return typeof meta[ field.id  ] !== 'undefined'
      }

      return !(configuration.options.some(option => option.value === meta[ field.id ] ));
    }
  );

  // Return object with meta keys to be updated. Unset if there is no configuration for the new value or no default for
  // that configuration.
  return needUpdate.reduce( ( updates, field ) => {
    const configuration = field.configurations[ value ];

    return Object.assign(
      updates,
      {[ field.id ]: configuration && configuration.default ? configuration.default : null}
    );
  }, {} );
};

export function fromThemeOptions( WrappedComponent ) {

  return class extends Component {
    render() {
      const { theme, ...ownProps } = this.props;

      if ( !theme ) {
        return <WrappedComponent { ...ownProps }/>
      }

      const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );

      const field = getFieldFromTheme( theme, ownProps.metaKey, meta );

      if ( !field || !field.options ) {
        return null;
      }

      const provideNewMeta = ( metaKey, value, meta ) => Object.assign(
        { [ metaKey ]: value },
        getDependencyUpdates( theme, metaKey, value, meta )
      );

      return <WrappedComponent getNewMeta={ provideNewMeta } defaultValue={ field.default } options={ field.options } { ...ownProps } />;
    }
  };
}
