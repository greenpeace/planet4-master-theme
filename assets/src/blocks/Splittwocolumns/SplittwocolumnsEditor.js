import { Fragment } from '@wordpress/element';
import { BLOCK_NAME, VERSION } from './SplittwocolumnsBlock';
import { SplittwocolumnsFrontend as Frontend } from './SplittwocolumnsFrontend';
import { SplittwocolumnsSettings as SidebarSettings } from './SplittwocolumnsSettings';
import { SplittwocolumnsInPlaceEdit as InPlaceEdit } from './SplittwocolumnsInPlaceEdit';

const { apiFetch } = wp;
const { useSelect } = wp.data;


const useImage = (image_id, current_url) => {
  const { image = {url: current_url} } = useSelect(select => {
    return image_id ? { image: select('core').getMedia(image_id) } : { image: null };
  });
  return image;
};

export const SplittwocolumnsEditor = ({ attributes, setAttributes, isSelected }) => {
  updateDeprecatedData(attributes, setAttributes, BLOCK_NAME, VERSION);
  const { issue_image_id, issue_image_src, tag_image_id, tag_image_src } = attributes;

  const issue_image = useImage(issue_image_id, issue_image_src);
  const tag_image = useImage(tag_image_id, tag_image_src);
  setAttributes({
    issue_image_src: issue_image?.source_url ?? issue_image?.url ?? '',
    issue_image_title: issue_image?.title?.raw ?? issue_image?.title ?? '',
    tag_image_src: tag_image?.source_url ?? tag_image?.url ?? '',
    tag_image_title: tag_image?.title?.raw ?? tag_image?.title ?? '',
  });

  return (
    isSelected
      ? renderEdit({attributes}, setAttributes) 
      : renderView({attributes})
  );
}

const renderView = ({attributes}) => <Frontend {...attributes} />
const renderEdit = ({attributes}, setAttributes) => {
  const charLimit = { title: 40, description: 400 };
  const params = {attributes, charLimit, setAttributes};

  return (
    <Fragment>
      <InPlaceEdit {...params} />
      <SidebarSettings  {...params} />
    </Fragment>
  );
}

// Query an updated version of attributes when version is deprecated
const updateDeprecatedData = (attributes, setAttributes, blockName, version) => {
  if (attributes.version && parseInt(attributes.version) >= version) {
    return;
  }

  apiFetch({
    path: 'planet4/v1/update_block/' + blockName,
    method: 'POST',
    data: attributes
  }).then(data => setAttributes(data));
}

// Remove some deprecated attributes
export const migrateAttributes = (attributes) => {
  if (attributes.issue_image || attributes.tag_image) {
    attributes.version = 1;
    attributes.issue_image_id = attributes.issue_image;
    attributes.tag_image_id = attributes.tag_image;

    delete attributes.issue_image;
    delete attributes.tag_image;
  }
  return attributes;
}