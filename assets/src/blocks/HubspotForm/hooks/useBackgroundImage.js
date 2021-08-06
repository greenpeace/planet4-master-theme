import { useState, useEffect } from '@wordpress/element';

export const useBackgroundImage = (image) => {
  const [ backgroundImage, setBackgroundImage ] = useState();

  useEffect(() => {
    setBackgroundImage({ backgroundImage: image ? `url(${image})` : 'none' });
  }, [ image ]);

  return backgroundImage;
};
