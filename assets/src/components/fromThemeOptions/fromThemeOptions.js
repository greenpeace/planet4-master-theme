import { Component } from '@wordpress/element';

const getFieldFromTheme = ( theme, fieldName ) => {
  if ( !theme ) {
    return null;
  }

  const field = theme.fields.find( field => field.id === fieldName );

  if ( !field ) {
    return null;
  }

  if ( field.dependsOn ) {
    return resolveDependency( theme, field );
  }

  return field;
};

const resolveDependency = ( theme, field ) => {
  const dependencyField = theme.fields.find( field2 => field2.id === field.dependsOn );

  if ( !dependencyField ) {
    return null;
  }

  const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );
  if ( !meta ) {
    return null;
  }

  const dependencyConfiguration = dependencyField.dependsOn ? resolveDependency( theme, dependencyField ) : dependencyField;

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

  return field.configurations[ dependencyValue ];
};

export function fromThemeOptions( WrappedComponent ) {

  return class extends Component {
    constructor(props) {
      super(props);
      this.state = {
        dependencyValue: null,
      };
    }

    componentDidMount() {
      const field = getFieldFromTheme( this.props.theme, this.props.metaKey );
      if ( !field ) {
        return;
      }
      if ( field.dependsOn ) {
        const dependencyField = theme.fields.find( field2 => field2.id === field.dependsOn );

        if ( !dependencyField ) {
          return;
        }

        wp.data.subscribe( () => {
          const meta = wp.data.select( 'core/editor' ).getEditedPostAttribute( 'meta' );
          if ( !meta ) {
            return;
          }
          const dependencyValue = meta[ field.dependsOn ] || dependencyField.default;

          if ( dependencyValue !== this.state.dependencyValue ) {
            this.setState( prevState => {
              return {
                dependencyValue: dependencyValue,
                ...prevState
              };
            } );
          }
        } );
      }
    }

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
