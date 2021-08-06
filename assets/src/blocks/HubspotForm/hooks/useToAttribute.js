export const useToAttribute = (setAttributes) => {
  const toAttribute = (attributeName, maxLength = -1) => value => {
    if(setAttributes) {
      if(maxLength > -1) {
        if(value.length < maxLength) {
          setAttributes({
            [attributeName]: value,
          });
        } else {
          setAttributes({
            [attributeName]: value.slice(0, maxLength),
          });
        }
      } else {
        setAttributes({
          [attributeName]: value,
        });
      }
    }
  };

  return toAttribute;
};
