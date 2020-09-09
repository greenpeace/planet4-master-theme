import { Fragment, Component } from '@wordpress/element';
import { COLOR_VALUE_REGEX, renderControl } from './renderControl';
import { IconButton } from '@wordpress/components';

const uniqueUsages = cssVar => {
  return [
    ...new Set(
      cssVar.usages.map(
        usage => usage.selector.replace( ',', ',\n' )
      )
    )
  ];
};

const uniqueProperties = cssVar => [ ...new Set( cssVar.usages.map( usage => usage.property ) ) ];

const removeVar = varName => document.documentElement.style.removeProperty(varName);

export class VariableControl extends Component {
  constructor(props) {
    super( props );
    this.state = {
      isOpen: false,
      showSelectors: false,
    };
    this.toggleSelectors = this.toggleSelectors.bind(this);
    this.toggleOpen = this.toggleOpen.bind(this);
    this.formatTitle = this.formatTitle.bind(this);
    this.showUsages = this.showUsages.bind(this);
  }

  toggleSelectors() {
    this.setState( { showSelectors: !this.state.showSelectors } )
  }

  previewValue() {
    const {value} = this.props;
    const size = '24px';

    if (value && value.match(COLOR_VALUE_REGEX)) {
      return <span
        onClick={ this.toggleOpen }
        title={value}
        style={ {
          width: size,
          height: size,
          border: '1px solid black',
          borderRadius: '6px',
          backgroundColor: value,
          float: 'right',
          marginTop: '2px'
        } }/>;
    }
    return <span
      onClick={ this.toggleOpen }
      title={value}
      style={ {
        // width: size,
        fontSize: '9px',
        height: size,
        float: 'right',
        marginTop: '2px'
      } }
    >
      { value }
    </span>;
  }

  toggleOpen() {
    this.setState( { isOpen: !this.state.isOpen } )
  }

  showUsages() {
    const renderCollapsed = () => <pre onClick={ this.toggleSelectors } className={'usages-collapsed'}>
      { uniqueUsages( this.props.cssVar ).join( ', ' ) }
    </pre>;

    const renderShow = () => <pre onClick={ this.toggleSelectors }>
        { uniqueUsages( this.props.cssVar ).join( '\n' ).replace( ',', ',\n' ) }
      </pre>;

    return <div
      style={ { fontSize: '11px', position: 'relative', marginTop: '16px' } }
    >
      <span
        onClick={ this.toggleSelectors }
        style={ {
          userSelect: 'none',
          fontSize: '10px',
          position: 'absolute',
          top: -12,
          left: 0
        } }
      >
        { uniqueUsages( this.props.cssVar ).length } selectors
      </span>
      { this.state.showSelectors ? renderShow() : renderCollapsed() }

    </div>;
  }

  formatTitle() {
    const capitalize = string=>string.charAt(0).toUpperCase() + string.slice(1)

    return capitalize(
      this.props.cssVar.name.replace( /^--/, '' ).replace(/--/g, ': ').replace( /[-_]/g, ' ' )
    );
  }

  render() {
    const {
      cssVar,
      value,
      onCloseClick,
      onChange,
    } = this.props;

    return <li
      key={ cssVar.name }
      className={'var-control'}
      style={ {
        userSelect: 'none',
        position: 'relative',
        listStyleType: 'none',
        fontSize: '15px',
        clear: 'both',
      } }
    >
      { !!onCloseClick && <IconButton
        icon={ 'minus' }
        style={ { float: 'right', height: '29px' } }
        onClick={ () => onCloseClick( cssVar ) }
      /> }
      { this.previewValue() }
      <h5
        style={ { fontSize: '16px', padding: '2px 4px 0', userSelect: 'text', fontWeight: '400' } }
        onClick={ this.toggleOpen }
      >
        {this.formatTitle()}
      </h5>
      { this.state.isOpen && (
        <Fragment>
          { this.showUsages() }
          <pre
            style={ { float: 'right', fontSize: '11px', paddingLeft: '8px', backgroundColor: '#eae896' } }
          >
              { uniqueProperties( cssVar ).join( ', ' ) }
            </pre>
          { renderControl( { cssVar, value, onChange } ) }
        </Fragment>
      ) }
    </li>;
  }
}
