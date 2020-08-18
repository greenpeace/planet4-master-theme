import { Fragment } from '@wordpress/element';
import { BLOCK_NAME, VERSION } from './SplittwocolumnsBlock';
import { SplittwocolumnsFrontend as Frontend } from './SplittwocolumnsFrontend';
import { SplittwocolumnsSettings as SidebarSettings } from './SplittwocolumnsSettings';
import { SplittwocolumnsInPlaceEdit as InPlaceEdit } from './SplittwocolumnsInPlaceEdit';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

export const SplittwocolumnsEditor = ({ attributes, setAttributes, isSelected }) => {
  updateDeprecatedData(attributes, setAttributes, BLOCK_NAME, VERSION);
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
    path: addQueryArgs('planet4/v1/update_block/' + blockName, attributes),
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