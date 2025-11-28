import metadata from './block.json';
import TopicLinkEditor from './edit';
import TopicLink from './topic-link';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

import './style.scss';

registerBlockType(metadata, {
  category: 'planet4-blocks',
  edit: props => (
    <div {...useBlockProps()}>
      <TopicLinkEditor {...props} />
    </div>
  ),
  save: ({attributes}) => (
    <div {...useBlockProps.save()}>
      <TopicLink {...attributes} />
    </div>
  ),
  deprecated: [{
    save() {
      return null;
    },
  }],
});
