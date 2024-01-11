import {BLOCK_NAME, attributes} from '../ENFormBlock';
import {frontendRendered} from '../../frontendRendered';

export const ENFormV2 = {
  attributes,
  save: props => {
    // Sort attributes in a predictable order
    const ordered_attrs = Object.fromEntries(Object.entries(props.attributes).sort());

    return frontendRendered(BLOCK_NAME)(ordered_attrs, props?.className);
  },
};
