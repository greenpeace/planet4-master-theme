import {CookiesFrontend} from './CookiesFrontend';

export const CookiesEditor = ({attributes, isSelected, setAttributes}) => {
  const toAttribute = attributeName => value => {
    if (isSelected) {
      setAttributes({[attributeName]: value});
    }
  };

  return (
    <CookiesFrontend
      {...attributes}
      isEditing
      toAttribute={toAttribute}
      isSelected={isSelected}
    />
  );
};
