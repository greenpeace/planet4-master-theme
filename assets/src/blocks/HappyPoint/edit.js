import {HappyPointEditor} from './HappyPointEditor.js';

const {useBlockProps} = wp.blockEditor;

const Edit = props => (
  <div {...useBlockProps()}>
    <HappyPointEditor {...props} />
  </div>
);

export default Edit;
