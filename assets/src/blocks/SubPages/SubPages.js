import {Fragment} from '@wordpress/element';
import {
  ServerSideRender,
} from '@wordpress/components';
import {Preview} from '../../components/Preview';

export const SubPages = props => {
  return (
    <Fragment>
      <Preview showBar={props.isSelected}>
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
