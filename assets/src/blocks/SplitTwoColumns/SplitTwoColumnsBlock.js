import {renderToString} from 'react-dom/server';
import {frontendRendered} from '../frontendRendered';
import {SplitTwoColumnsEditor} from './SplitTwoColumnsEditor';
import {SplitTwoColumnsFrontend} from './SplitTwoColumnsFrontend';
import {splitTwoColumnsV1} from './deprecated/splitTwoColumnsV1';

export const BLOCK_NAME = 'planet4-blocks/split-two-columns';
export const VERSION = 2;

const attributes = {
  version: {type: 'number', default: VERSION},
  select_issue: {type: 'number', default: 0},
  title: {type: 'string', default: ''},
  issue_description: {type: 'string', default: ''},
  issue_link_text: {type: 'string', default: ''},
  issue_link_path: {type: 'string', default: ''},
  issue_image_id: {type: 'number', default: 0},
  issue_image_src: {type: 'string', default: ''},
  issue_image_srcset: {type: 'string', default: ''},
  issue_image_title: {type: 'string', default: ''},
  focus_issue_image: {type: 'string', default: '50% 50%'},
  select_tag: {type: 'number', default: 0},
  tag_name: {type: 'string', default: ''},
  tag_description: {type: 'string', default: ''},
  tag_link: {type: 'string', default: ''},
  button_text: {type: 'string', default: ''},
  button_link: {type: 'string', default: ''},
  tag_image_id: {type: 'number', default: 0},
  tag_image_src: {type: 'string', default: ''},
  tag_image_srcset: {type: 'string', default: ''},
  tag_image_title: {type: 'string', default: ''},
  focus_tag_image: {type: 'string', default: '50% 50%'},
  edited: {type: 'object', default: {
    title: false,
    issue_description: false,
    issue_link_text: false,
    tag_description: false,
    button_text: false,
    issue_image_id: false,
    tag_image_id: false,
  }},
};

export const registerSplitTwoColumnsBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {RawHTML} = wp.element;

  registerBlockType(BLOCK_NAME, {
    title: 'Split Two Columns',
    icon: 'editor-table',
    category: 'planet4-blocks',
    attributes,
    edit: SplitTwoColumnsEditor,
    save: props => {
      const markup = renderToString(<div
        data-hydrate={BLOCK_NAME}
        data-attributes={JSON.stringify(props.attributes)}
      >
        <SplitTwoColumnsFrontend {...props} />
      </div>);
      return <RawHTML>{markup}</RawHTML>;
    },
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    deprecated: [
      {
        attributes,
        save: frontendRendered(BLOCK_NAME),
      },
      splitTwoColumnsV1,
    ],
  });
};
