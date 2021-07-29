import {Component, Fragment} from '@wordpress/element';
import {
  ServerSideRender
} from '@wordpress/components';
import {Preview} from '../../components/Preview';

export class SubPages extends Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <Fragment>
        <Preview showBar={this.props.isSelected}>
          <ServerSideRender
            block={'planet4-blocks/sub-pages'}
            attributes={{}}
            urlQueryArgs={{post_id: document.querySelector('#post_ID').value}}
          >
          </ServerSideRender>
        </Preview>
      </Fragment>
    );
  };
}
