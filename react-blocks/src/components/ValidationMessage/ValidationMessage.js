import {React, Component} from 'react';

export class ValidationMessage extends Component {
  constructor(props) {
    super(props);
  }
  render() {
    return <div className='ValidationMessage'>
      <ul>
      {this.props.message.map((validation_message, key) =>
        <li key={key}> {validation_message} </li>
      )}
      </ul>
    </div>;
  }
}
