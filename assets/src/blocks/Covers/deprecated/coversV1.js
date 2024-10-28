import {COMMON_ATTRIBUTES, COVERS_LAYOUTS} from '../CoversConstants';

const OLD_COVER_TYPES = {
  1: 'take-action',
  2: 'campaign',
  3: 'content',
};

export const coversV1 = {
  attributes: {
    ...COMMON_ATTRIBUTES,
    title: {
      type: 'string',
    },
    description: {
      type: 'string',
    },
    covers_view: {
      type: 'string',
      default: '1',
    },
    cover_type: {
      type: 'string',
    },
  },
  isEligible({covers_view, cover_type, layout}) {
    return covers_view || !isNaN(cover_type) || !layout;
  },
  migrate({covers_view, cover_type, layout, ...attributes}) {
    attributes.version = 1;
    attributes.initialRowsLimit = covers_view === '3' ? 0 : Number(covers_view);

    if (!isNaN(cover_type)) {
      attributes.cover_type = OLD_COVER_TYPES[cover_type];
    } else {
      attributes.cover_type = cover_type;
    }

    if (!layout) {
      attributes.layout = COVERS_LAYOUTS.grid;
    }

    return attributes;
  },
  save: () => null,
};
