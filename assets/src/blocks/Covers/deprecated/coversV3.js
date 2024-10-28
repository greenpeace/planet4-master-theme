import {frontendRendered} from '../../../functions/frontendRendered';
import {ATTRIBUTES, BLOCK_NAME} from '../CoversConstants';

const {__} = wp.i18n;

export const coversV3 = {
  attributes: {
    ...ATTRIBUTES,
    readMoreText: {
      type: 'string',
      default: __('Load more', 'planet4-blocks'),
    },
  },
  save: frontendRendered(BLOCK_NAME),
};
