import {Fragment} from '@wordpress/element';
import {BLOCK_NAME, VERSION} from './register';
import {SplitTwoColumnsFrontend} from './SplitTwoColumnsFrontend';
import {SplitTwoColumnsSettings} from './SplitTwoColumnsSettings';
import {SplitTwoColumnsInPlaceEdit} from './SplitTwoColumnsInPlaceEdit';

const {apiFetch} = wp;
const {useSelect} = wp.data;

const useImage = (image_id, url) => {
  return useSelect(select => !image_id ? {url} : select('core').getMedia(image_id));
};

export const SplitTwoColumnsEditor = ({attributes, setAttributes, isSelected}) => {
  // Todo: Never directly change things in a render, this will cause issues. Render should only render or attach listeners.
  updateDeprecatedData(attributes, setAttributes, BLOCK_NAME, VERSION);

  const {
    issue_image_id,
    issue_image_src,
    tag_image_id,
    tag_image_src,
  } = attributes;

  const issue_image = useImage(issue_image_id, issue_image_src);
  const tag_image = useImage(tag_image_id, tag_image_src);

  // Todo: Never directly change things in a render, this will cause issues. Render should only render or attach listeners.
  setAttributes({
    issue_image_src: issue_image?.source_url ?? issue_image?.url ?? '',
    issue_image_title: issue_image?.title?.raw ?? issue_image?.title ?? '',
    tag_image_src: tag_image?.source_url ?? tag_image?.url ?? '',
    tag_image_title: tag_image?.title?.raw ?? tag_image?.title ?? '',
  });

  return (
    isSelected ?
      renderEdit({attributes}, setAttributes) :
      renderView({attributes})
  );
};

const renderView = ({attributes}) => <SplitTwoColumnsFrontend {...attributes} />;
const renderEdit = ({attributes}, setAttributes) => {
  const charLimit = {title: 40, description: 400};
  const params = {attributes, charLimit, setAttributes};

  return (
    <Fragment>
      <SplitTwoColumnsInPlaceEdit {...params} />
      <SplitTwoColumnsSettings {...params} />
    </Fragment>
  );
};

// Query an updated version of attributes when version is deprecated
const updateDeprecatedData = (attributes, setAttributes, blockName, version) => {
  if (attributes.version && parseInt(attributes.version) >= version) {
    return;
  }

  apiFetch({
    path: 'planet4/v1/update_block/' + blockName,
    method: 'POST',
    data: attributes,
  }).then(data => setAttributes(data));
};
