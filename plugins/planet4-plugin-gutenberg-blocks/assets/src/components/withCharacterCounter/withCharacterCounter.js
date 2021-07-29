import { Component } from '@wordpress/element';
import classNames from 'classnames';

const withCharacterCounter = ( WrappedComponent ) => {
  class WrappingComponent extends Component {
    constructor( props ) {
      super( props );

      this.state = {
        charactersUsed: props.value ? props.value.length : 0,
      };

      this.handleChange = this.handleChange.bind( this );
      this.updateCharactersUsed = this.updateCharactersUsed.bind( this );
    }

    updateCharactersUsed( value ) {
      this.setState( () => {
        return { charactersUsed: value.length };
      } );
    }

    handleChange( value ) {
      if ( this.props.onChange ) {
        this.props.onChange( value );
      }
      this.updateCharactersUsed( value );
    }

    shouldShowWarning() {
      return this.props.characterLimit
        && !this.exceededLimit()
        && this.props.characterLimit - this.state.charactersUsed < this.warningThreshold();
    }

    warningThreshold() {
      const tenthOfLimit = this.props.characterLimit / 10;

      // round down to nearest 10
      const roundTo = 5;

      return Math.ceil( tenthOfLimit / roundTo ) * roundTo;
    }

    exceededLimit() {
      return this.props.characterLimit && this.state.charactersUsed > this.props.characterLimit;
    }

    showCounter() {
      // Force to return only boolean with `!!` or it will get rendered.
      return !!this.props.characterLimit;
    }

    render() {
      const { characterLimit, warningThreshold, onChange, ...passThroughProps } = this.props;

      const getClassnames = () => classNames(
        'character-counter',
        {
          'character-limit-exceeded': this.exceededLimit(),
          'character-limit-warning': this.shouldShowWarning(),
        }
      );

      return (
        <div className="counted-field-wrapper">
          <WrappedComponent
            onChange={ this.handleChange }
            { ...passThroughProps }
          />
          { this.showCounter() &&
          <div className={ getClassnames() }>
            <span>{ this.state.charactersUsed }</span>
            <span>/{ characterLimit }</span>
          </div>
          }
        </div>
      );
    }
  }

  return WrappingComponent;
};

export default withCharacterCounter;
