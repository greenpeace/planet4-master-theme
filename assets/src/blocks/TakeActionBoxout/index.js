import {TakeActionBoxoutEditor} from './edit.js';
import {takeActionBoxoutV1} from './deprecated/takeActionBoxoutV1.js';
import metadata from './block.json';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

import './style.scss';

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
