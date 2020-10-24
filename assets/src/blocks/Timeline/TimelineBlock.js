import {frontendRendered} from '../frontendRendered';
import {TimelineEditor} from './TimelineEditor';

const {__} = wp.i18n;
const BLOCK_NAME = 'planet4-blocks/timeline';

const attributes = {
  timeline_title: {
    type: 'string',
    default: ''
  },
  description: {
    type: 'string',
    default: ''
  },
  google_sheets_url: {
    type: 'string',
    default: ''
  },
  language: {
    type: 'string',
    default: 'en',
  },
  timenav_position: {
    type: 'string',
    default: ''
  },
  start_at_end: {
    type: 'boolean',
    default: false
  },
};

export const registerTimelineBlock = () => {
  const {registerBlockType} = wp.blocks;

  registerBlockType(BLOCK_NAME, {
    title: __('Timeline', 'p4ge'),
    icon: 'clock',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: TimelineEditor,
    save: frontendRendered(BLOCK_NAME),
    deprecated: [
      {
        attributes,
        save() {
          return null;
        }
      }
    ]
  });
};
