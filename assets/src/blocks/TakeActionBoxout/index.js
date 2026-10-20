import {TakeActionBoxoutEditor} from './edit.js';
import {takeActionBoxoutV1} from './deprecated/takeActionBoxoutV1.js';
import metadata from './block.json';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <TakeActionBoxoutEditor {...props} />
    </div>
  ),
  save() {
    return null;
  },
  deprecated: [
    takeActionBoxoutV1,
  ],
});
