import {Component} from '@wordpress/element';
import {RadioControl} from '@wordpress/components';

export class LayoutSelector extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedOption: props.selectedOption,
    };
    this.setSelected = this.setSelected.bind(this);
  }

  setSelected(option) {
    this.setState({selectedOption: option});
    this.props.onSelectedLayoutChange(option);
  }

  render() {
    return <div className="LayoutSelector">
      {
        this.props.options.map((layoutOption, i) => {
          if ('number' === typeof layoutOption.value) {
            this.setState({
              selectedOption: Number(this.state.selectedOption),
            });
          }

          return (
            <label className="LayoutOption" key={i} htmlFor="layout-selector__control-1">
              <div style={{display: 'flex'}}>
                <RadioControl
                  id="layout-selector__control-1"
                  name={'layoutOption'}
                  selected={this.state.selectedOption}
                  options={[
                    {value: layoutOption.value},
                  ]}
                  onChange={this.setSelected}
                />
                {layoutOption.label}
              </div>
              {
                layoutOption.image ?
                  <img src={layoutOption.image} alt="" /> :
                  null
              }
              {
                layoutOption.help ?
                  <p className="help" dangerouslySetInnerHTML={{__html: layoutOption.help}} /> :
                  null
              }
            </label>
          );
        })
      }
    </div>;
  }
}
