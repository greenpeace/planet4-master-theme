import metadata from './block.json';
import TopicLinkEditor from './edit';
import TopicLink from './topic-link';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
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
    attributes: metadata.attributes,
    save() {
      return null;
    },
  }],
});
