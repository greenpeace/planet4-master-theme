import metadata from './block.json';
import {CarouselHeaderEditor} from './edit';
import {save} from './save';
import {deprecated} from './deprecated';

import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <CarouselHeaderEditor {...props} />
    </div>
  ),
  save,
  deprecated,
});
