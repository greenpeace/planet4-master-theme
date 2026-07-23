import {renderToString} from 'react-dom/server';
import {TimelineEditor} from './TimelineEditorScript';
import {TimelineFrontend} from './TimelineFrontend';
import {frontendRendered} from '../../functions/frontendRendered';

const BLOCK_NAME = 'planet4-blocks/timeline';

export const registerTimelineBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {__} = wp.i18n;
  const {RawHTML} = wp.element;
  const {useBlockProps} = wp.blockEditor;

  const attributes = {
    timeline_title: {
      type: 'string',
      default: '',
    },
    description: {
      type: 'string',
      default: '',
    },
    google_sheets_url: {
      type: 'string',
      default: '',
    },
    language: {
      type: 'string',
      default: 'en',
    },
    timenav_position: {
      type: 'string',
      default: '',
    },
    start_at_end: {
      type: 'boolean',
      default: false,
    },
    timeline_id: {
      type: 'string',
      default: '',
    },
  };

  registerBlockType(BLOCK_NAME, {
    title: 'Timeline',
    description: __('A type of graphic that arranges a chain of events, activities, and milestones in chronological order.', 'planet4-master-theme-backend'),
    icon: 'clock',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: props => (
      <div {...useBlockProps()}>
        <TimelineEditor {...props} />
      </div>
    ),
    save: props => {
      const markup = renderToString(
        <div
          data-hydrate={BLOCK_NAME}
          data-attributes={JSON.stringify(props.attributes)}
        >
          <TimelineFrontend {...props} />
        </div>
      );
      return <RawHTML>{markup}</RawHTML>;
    },
    deprecated: [
      {
        attributes,
        save: frontendRendered(BLOCK_NAME),
      },
      {
        attributes,
        save() {
          return null;
        },
      },
    ],
  });
};
