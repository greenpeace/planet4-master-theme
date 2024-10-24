import {frontendRendered} from '../../../functions/frontendRendered';
import {BLOCK_NAME, attributes} from '../CoversConstants';

export const coversV3 = {
  attributes,
  save: frontendRendered(BLOCK_NAME),
};
