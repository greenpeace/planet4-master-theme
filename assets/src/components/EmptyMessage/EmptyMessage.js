import {Component} from '@wordpress/element';

export class EmptyMessage extends Component {
  render() {
    return <div className='EmptyMessage'>
			{ `This block has not enough data to be rendered yet.` }
    </div>;
  }
}
