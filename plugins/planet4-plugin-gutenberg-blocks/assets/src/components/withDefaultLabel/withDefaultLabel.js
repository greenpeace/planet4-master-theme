import { Component } from '@wordpress/element';

function getDisplayName(WrappedComponent) {
  return WrappedComponent.displayName || WrappedComponent.name || 'Component';
}

export function withDefaultLabel( WrappedComponent ) {
  const {__} = wp.i18n;

  class WithDefaultLabelHOC extends Component {
    constructor( props ) {
      super( props );

      this.defaultLabel = __('(default)');
    }

    render() {
      const { options, ...ownProps } = this.props;

      const enhancedOptions = !options ? [] : options.map( option => {
        const label = option.value === this.props.defaultValue ? option.label + ' ' + this.defaultLabel : option.label
        return {
            ...option,
           label: label,
        }
      });

      return <WrappedComponent
        options={ enhancedOptions }
        { ...ownProps }
      />;
    }
  }

  WithDefaultLabelHOC.displayName = `WithDefaultLabelHOC(${getDisplayName(WrappedComponent)})`;

  return WithDefaultLabelHOC;
}
