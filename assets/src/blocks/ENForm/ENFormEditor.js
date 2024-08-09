import {ENFormInPlaceEdit} from './ENFormInPlaceEdit';
import {ENFormSettings} from './ENFormSettings';
import {getStyleFromClassName} from '../../functions/getStyleFromClassName';

const {useSelect} = wp.data;

export const ENFormEditor = ({attributes, setAttributes}) => {
  return (
    renderEdit(attributes, setAttributes)
  );
};

const renderEdit = (attributes, setAttributes) => {
  const {en_form_style, className, background, background_image_src} = attributes;

  if (className && className.length > 0) {
    setAttributes({
      en_form_style: getStyleFromClassName(className),
    });
  }

  if (!en_form_style || en_form_style.length <= 0) {
    setAttributes({en_form_style: 'side-style'});
  }

  // Retrieve background for legacy blocks
  if (background > 0 && background_image_src.length <= 0) {
    setAttributes({
      background_image_src: useSelect(select => {
        const img = select('core').getMedia(background);
        return img?.source_url || '';
      }),
    });
  }

  const charLimit = {title: 40, description: 400};
  const params = {attributes, charLimit, setAttributes};

  return (
    <>
      <ENFormInPlaceEdit {...params} />
      <ENFormSettings {...params} />
    </>
  );
};

