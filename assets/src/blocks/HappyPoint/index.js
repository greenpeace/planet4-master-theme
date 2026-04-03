import metadata from './block.json';
import {HappyPointEditor} from './HappyPointEditor.js';
import {HappyPointBlock as HappyPointBlockV1} from './deprecated/HappyPointBlockV1.js';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <HappyPointEditor {...props} />
    </div>
  ),
  save: () => null,
  deprecated: [HappyPointBlockV1],
});
