import {React, Component} from 'react';
import {
  CheckboxControl,
  SelectControl,
} from '@wordpress/components';

export class MenuLevel extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <div>
        <div>Level {this.props.index + 1}</div>
        <SelectControl
          label="Submenu item"
          help="Submenu item"
          value={this.props.heading}
          options={[
            {label: 'None', value: '0'},
            {label: 'Heading 1', value: '1'},
            {label: 'Heading 2', value: '2'},
            {label: 'Heading 3', value: '3'},
            {label: 'Heading 4', value: '4'},
            {label: 'Heading 5', value: '5'},
            {label: 'Heading 6', value: '6'},
          ]}
          onChange={(e) => this.props.onHeadingChange(this.props.index, e)}
          className='submenu-block-attribute-wrapper'
        />

        <CheckboxControl
          heading="Link"
          help="Link item"
          value={this.props.link}
          checked={this.props.link}
          onChange={(e) => this.props.onLinkChange(this.props.index, e)}
          className="submenu-level-link"
        />

        <SelectControl
          label="List style"
          help="List style"
          value={this.props.style}
          options={[
            {label: 'None', value: 'none'},
            {label: 'Bullet', value: 'bullet'},
            {label: 'Number', value: 'number'},
          ]}
          onChange={(e) => this.props.onStyleChange(this.props.index, e)}
          className='submenu-block-attribute-wrapper'
        />
      </div>
    );
  };
}
