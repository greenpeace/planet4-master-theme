import {ATTRIBUTES} from '../CoversConstants';

const {__} = wp.i18n;

export const coversV2 = {
  attributes: {
    ...ATTRIBUTES,
  },
  isEligible({readMoreText}) {
    return !readMoreText;
  },
  migrate({className, ...attributes}) {
    return {
      ...attributes,
      readMoreText: __('Load more', 'planet4-blocks'),
      className,
    };
  },
  save: () => null,
};
