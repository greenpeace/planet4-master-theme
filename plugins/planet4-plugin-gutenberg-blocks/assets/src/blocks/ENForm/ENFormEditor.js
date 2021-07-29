import { ENFormFrontend } from './ENFormFrontend';
import { ENFormInPlaceEdit } from './ENFormInPlaceEdit';
import { ENFormSettings } from './ENFormSettings';
import { getStyleFromClassName } from '../getStyleFromClassName';

import { useSelect } from '@wordpress/data';


export const ENFormEditor = ({ attributes, setAttributes, isSelected }) => {

  const { en_form_style, className } = attributes;

  console.log('1', en_form_style, className);

  if ( className && className.length > 0 ) {
    setAttributes({
      en_form_style: getStyleFromClassName(className)
    });
  }

  if (! en_form_style || en_form_style.length <= 0) {
    setAttributes({en_form_style: 'side-style'});
  }

  console.log('2', en_form_style, className);

  return (
    isSelected
      ? renderEdit({attributes}, setAttributes)
      : renderView({attributes})
  );
}

const renderView = ({attributes}) => <ENFormFrontend {...attributes} />

const renderEdit = ({attributes}, setAttributes) => {
  const charLimit = { title: 40, description: 400 };
  const params = {attributes, charLimit, setAttributes};

  // Retrieve background for legacy blocks
  const { background, background_image_src } = attributes;
  if (background > 0 && background_image_src.length <= 0) {
    setAttributes({
      background_image_src: useSelect((select) => {
        const img = select('core').getMedia(background);
        return img?.source_url || '';
      })
    })
  }

  return (
    <>
      <ENFormInPlaceEdit {...params} />
      <ENFormSettings  {...params} />
    </>
  );
}

