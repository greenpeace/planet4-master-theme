import metadata from './block.json';
import {SocialMediaEditor} from './edit.js';
import {socialMediaV1} from './deprecated/socialMediaV1.js';
import {SocialMediaFrontend} from './SocialMediaFrontend.js';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {useBlockProps} = wp.blockEditor;

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <SocialMediaEditor {...props} />
    </div>
  ),
  save: ({attributes}) => (
    <div {...useBlockProps.save()}>
      <SocialMediaFrontend {...attributes} />
    </div>
  ),
  deprecated: [socialMediaV1],
});
