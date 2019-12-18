import {Component} from '@wordpress/element';

export class InlineFormFeedback extends Component {
  render() {
    return <div className='InlineFormFeedback'>{this.props.children}</div>;
  }
}
