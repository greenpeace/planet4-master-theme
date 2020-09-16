import { useState, useEffect } from '@wordpress/element';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

export const useHappypointImageData = imageId => {
  const [imageData, setImageData] = useState({});

  useEffect(() => {
    const loadImageData = async () => {
      try {
        const queryArgs = {
          path: addQueryArgs('/planet4/v1/get-happypoint-data', {
            id: imageId
          })
        };

        const data = await apiFetch(queryArgs);
        setImageData(data);
      } catch (e) {
        console.log(e);
      }
    };
    loadImageData();
  }, [imageId]);

  return { imageData };
};
