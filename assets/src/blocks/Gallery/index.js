import metadata from './block.json';
import {renderToString} from 'react-dom/server';
import {GalleryEditor} from './edit';
import {frontendRendered} from '../../functions/frontendRendered';
import {GalleryFrontend} from './GalleryFrontend';
import './style.scss';

const {registerBlockType} = wp.blocks;
const {RawHTML} = wp.element;
const {useBlockProps} = wp.blockEditor;

const BLOCK_NAME = 'planet4-blocks/gallery';

registerBlockType(metadata, {
  edit: props => (
    <div {...useBlockProps()}>
      <GalleryEditor {...props} />
    </div>
  ),
  save: props => {
    const {attributes: saveAttributes} = props;
    const markup = renderToString(
      <div
        data-hydrate={BLOCK_NAME}
        data-attributes={JSON.stringify(saveAttributes)}
      >
        <GalleryFrontend {...props} />
      </div>
    );
    return <div {...useBlockProps.save()}><RawHTML>{ markup }</RawHTML></div>;
  },
  deprecated: [
    {
      attributes: metadata.attributes,
      edit: GalleryEditor,
      save: frontendRendered(BLOCK_NAME),
    },
    {
      attributes: metadata.attributes,
      save() {
        return null;
      },
    },
  ],
});
