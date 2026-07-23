import {renderToString} from 'react-dom/server';
import {TimelineEditor} from './TimelineEditorScript';
import {NewTimelineEditor} from './NewTimelineEditorScript';
import {TimelineFrontend} from './TimelineFrontend';
import {NewTimelineFrontend} from './NewTimelineFrontend';
import {frontendRendered} from '../../functions/frontendRendered';
import {getUniqueId} from '../../functions/getUniqueId';

const BLOCK_NAME = 'planet4-blocks/timeline';

const newTimelineEnabled = Boolean(window.p4_vars.features.new_timeline_block);

export const registerTimelineBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {__} = wp.i18n;
  const {RawHTML} = wp.element;
  const {useBlockProps} = wp.blockEditor;

  const blockAttributes = {
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
  };

  registerBlockType(BLOCK_NAME, {
    title: 'Timeline',
    description: __('A type of graphic that arranges a chain of events, activities, and milestones in chronological order.', 'planet4-master-theme-backend'),
    icon: 'clock',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes: newTimelineEnabled ? {
      ...blockAttributes,
      timeline_id: {type: 'string', default: ''},
    } : blockAttributes,
    edit: props => (
      <div {...useBlockProps()}>
        {newTimelineEnabled ? <NewTimelineEditor {...props} /> : <TimelineEditor {...props} />}
      </div>
    ),
    save: props => {
      const markup = renderToString(
        <div
          data-hydrate={BLOCK_NAME}
          data-attributes={JSON.stringify(props.attributes)}
        >
          {newTimelineEnabled ? <NewTimelineFrontend {...props} /> : <TimelineFrontend {...props} />}
        </div>
      );
      return <RawHTML>{markup}</RawHTML>;
    },
    deprecated: [
      newTimelineEnabled ? {
        attributes: blockAttributes,
        edit: TimelineEditor,
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
        isEligible(attributes) {
          return !attributes.timeline_id;
        },
        migrate(attributes) {
          return {
            ...attributes,
            timeline_id: getUniqueId('tl'),
          };
        },
      } : {},
      {
        attributes: blockAttributes,
        save: frontendRendered(BLOCK_NAME),
      },
      {
        attributes: blockAttributes,
        save() {
          return null;
        },
      },
    ],
  });
};
