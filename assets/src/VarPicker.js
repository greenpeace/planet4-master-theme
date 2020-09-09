import { Component } from '@wordpress/element';
import { VariableControl } from './VariableControl';

export const STORAGE_KEY = 'p4-theme';

export const readProperty = name => {
  const value = document.documentElement.style.getPropertyValue( name )

  console.log( 'reading', value );

  return value;
};

const sortVars = ( a, b ) => a.name > b.name ? 1 : ( a.name === b.name ? 0 : -1 );

const highlightClass = 'theme-editor-highlight';

const addHighlight = element => element.classList.add(highlightClass);

const removeHighlight = element => element.classList.remove(highlightClass);

export class VarPicker extends Component {
  constructor(props) {
    super(props);
    this.state = {
      changingPropertyTo: {},
      activeVars: this.props.selectedVars,
      collapsed: false,
      openGroups: [],
      shouldGroupVars: true,
    };
    this.deactivate = this.deactivate.bind(this);
    this.setProperty = this.setProperty.bind(this);
    this.toggleCollapsed = this.toggleCollapsed.bind(this);
    this.toggleGroup = this.toggleGroup.bind(this);
    this.isGroupOpen = this.isGroupOpen.bind(this);
  }

  componentWillReceiveProps( nextProps ) {
    const notAlreadyActive = cssVar => !this.state.activeVars.map( active => active.name ).includes( cssVar.name );

    const newOnes = nextProps.selectedVars.filter( notAlreadyActive );

    this.setState( {
      activeVars: [ ...this.state.activeVars, ...newOnes ],
    } );
  }

  deactivate( cssVar ) {
    const notBeingDeactivated = active => active.name !== cssVar.name;

    this.setState( {
      activeVars: this.state.activeVars.filter( notBeingDeactivated )
    } );
  }

  setProperty( name, value ) {
    const prevChangingTo = this.state.changingPropertyTo

    this.setState( {
      changingPropertyTo: {
        ...this.state.changingPropertyTo,
        [ name ]: value,
      }
    } );

    console.log( `Setting property \`${ name }\` to \`${ value }\`` );

    document.documentElement.style.setProperty( name, value );

    let fromStorage;
    try {
      fromStorage = JSON.parse(localStorage.getItem(STORAGE_KEY));
    } catch ( e ) {
      fromStorage = {};
    }

    const withNewVar = {
      ...fromStorage,
      [name]: value,
    }

    localStorage.setItem( STORAGE_KEY, JSON.stringify( withNewVar ) );

    this.setState({ changingPropertyTo: prevChangingTo })
  }

  toggleCollapsed() {
    this.setState({ collapsed: !this.state.collapsed})
  }

  closeAllButton() {
    return <span
      style={ { float: 'right', fontSize: '11px', border: '1px solid black' } }
      onClick={ () => this.setState( { activeVars: [] } ) }
    >
        Close all.
    </span>;
  }
  toggleGroup(id) {
    const newGroups = this.state.openGroups.includes(id)
      ? this.state.openGroups.filter(openId=> openId !== id)
      : [...this.state.openGroups, id];

    this.setState({ openGroups: newGroups})
  }

  isGroupOpen(label) {
    return this.state.openGroups.includes(label);
  }

  render() {
    return <div className={ 'var-picker' }>
      <span id={ 'drag-me' }>
        showing { this.state.activeVars.length } var{ this.state.activeVars.length === 1 ? '' : 's' }
      </span>
      <span
        style={ { fontSize: '10px', border: '1px solid grey', borderRadius: '3px', margin: '0 8px', padding: '2px 4px' } }
        onClick={ this.toggleCollapsed }
      >
        { this.state.collapsed ? 'show' : 'hide' }
      </span>
      <label
        htmlFor=""
        onClick={()=>this.setState({shouldGroupVars: !this.state.shouldGroupVars})}
        style={{marginBottom: '2px'}}
      >
        <input type="checkbox" checked={this.state.shouldGroupVars}/>
        { 'Only last clicked element' }
      </label>
      { !this.state.shouldGroupVars && this.state.activeVars.length > 0 && this.closeAllButton()}
      { this.state.shouldGroupVars && !this.state.collapsed && <ul>
        { this.props.groups.map(({element, label, vars})=> (
          <li className={'var-group'} key={ label } style={ { marginBottom: '12px' } }>
            <h4
              onClick={ () => this.toggleGroup(label) }
              onMouseEnter={ () => addHighlight(element) }
              onMouseLeave={ () => removeHighlight(element) }
            >
              { label } ({ vars.length })
            </h4>
            { this.isGroupOpen(label) && <ul>
              { vars.map(cssVar=>
                <VariableControl
                  cssVar={ cssVar }
                  value={
                    this.state.changingPropertyTo[ cssVar.name ]
                    || readProperty( cssVar.name )
                    || cssVar.usages.find( usage => !!usage.defaultValue ).defaultValue
                  }
                  onChange={ value => this.setProperty( cssVar.name, value ) }
                />
              )}
            </ul> }
          </li>
        ))}
      </ul> }
      { !this.state.shouldGroupVars && !this.state.collapsed && <ul>
          { this.state.activeVars.sort( sortVars ).map( cssVar =>
            <VariableControl
              cssVar={ cssVar }
              value={
                this.state.changingPropertyTo[ cssVar.name ]
                || readProperty( cssVar.name )
                || cssVar.usages.find( usage => !!usage.defaultValue ).defaultValue
              }
              onCloseClick={ this.deactivate }
              onChange={ value => this.setProperty( cssVar.name, value ) }
            />
          ) }
        </ul> }

    </div>;
  }
}
