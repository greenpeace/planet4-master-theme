import {React, Component} from 'react';
import {RadioControl} from '@wordpress/components';

export class LayoutSelector extends Component {
  constructor(props) {
    super(props);
    this.state = {
      selectedOption: props.selectedOption
    };
    this.setSelected = this.setSelected.bind(this);
  }

  setSelected(option) {
    this.setState({selectedOption: option});
    this.props.onSelectedLayoutChange(option);
  }

  render() {
    return <div className='LayoutSelector'>
      {
        this.props.options.map((layoutOption, i) => {

          if ( 'number' == typeof layoutOption.value ) {
            this.state.selectedOption = Number( this.state.selectedOption );
          }

          return (
            <label className='LayoutOption' key={i}>
              <div style={{display: 'flex'}}>
                <RadioControl
                  name={'layoutOption'}
                  selected={this.state.selectedOption}
                  options={[
                    {value: layoutOption.value}
                  ]}
                  onChange={this.setSelected}
                />
                {layoutOption.label}
              </div>
              {
                layoutOption.image
                  ? <img src={layoutOption.image}/>
                  : null
              }
              {
                layoutOption.help
                  ? <p className='help' dangerouslySetInnerHTML={{__html: layoutOption.help}}/>
                  : null
              }
            </label>
          )
        })
      }
    </div>;
  }
}
