import {fetchJson} from '../../functions/fetchJson';
import {addQueryArgs} from '../../functions/addQueryArgs';

const {apiFetch} = wp;
const {useState, useEffect} = wp.element;

export const useHappypointImageData = imageId => {
  const [imageData, setImageData] = useState({});

  useEffect(() => {
    const loadImageData = async () => {
      try {
        const args = {
          id: imageId,
        };

        const baseUrl = document.body.dataset.nro;

        const data = baseUrl ?
          await fetchJson(`${baseUrl}/wp-json/${addQueryArgs('planet4/v1/get-happypoint-data', args)}`) :
          await apiFetch({path: addQueryArgs('planet4/v1/get-happypoint-data', args)});

        setImageData(data);
      } catch (e) {
        // eslint-disable-next-line no-console
        console.log(e);
      }
    };
    loadImageData();
  }, [imageId]);

  return {imageData};
};
