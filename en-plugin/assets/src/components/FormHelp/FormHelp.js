import {Component} from '@wordpress/element';

export class FormHelp extends Component {
  render() {
    return <div className='FormHelp'>{this.props.children}</div>;
  }
}
