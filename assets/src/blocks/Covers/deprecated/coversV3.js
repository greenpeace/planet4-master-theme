import {frontendRendered} from '../../../functions/frontendRendered';
import {attributes} from '../CoversBlock';
import {BLOCK_NAME} from '../CoversConstants';

export const coversV3 = {
  attributes,
  save: frontendRendered(BLOCK_NAME),
};
