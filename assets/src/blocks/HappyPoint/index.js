import metadata from './block.json';
import Edit from './edit';
import save from './save';
import deprecated from './deprecated';

const {registerBlockType} = wp.blocks;

registerBlockType(metadata, {
  edit: Edit,
  save,
  deprecated,
});
