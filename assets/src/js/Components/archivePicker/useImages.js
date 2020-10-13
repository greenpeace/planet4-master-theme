import { useState } from '@wordpress/element';

const { apiFetch } = wp;
const { addQueryArgs } = wp.url;

export const useImages = () => {
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(false);
  const [processingImages, setProcessingImages] = useState(false);
  const [processingError, setProcessingError] = useState(null);
  const [error, setError] = useState(null);

  const loadPage = async (pageIndex, searchedText) => {
    if (loading) {
      return;
    }
    setLoading(true);

    try {
      const nextImages = await apiFetch({
        path: addQueryArgs('/planet4/v1/image-archive/fetch', {
          page: pageIndex,
          search_text: searchedText,
        })
      });
      setImages([
        ...(pageIndex === 1 ? [] : images),
        ...nextImages
      ]);
    } catch (error) {
      setError(error);
    } finally {
      setLoading(false);
    }
  };

  const includeInWp = async ids => {
    try {
      setProcessingImages(true);
      const updatedImages = await apiFetch({
        method: 'POST',
        path: '/planet4/v1/image-archive/transfer',
        data: {
          ids: ids,
          use_original_language: false,
        }
      });
      updateFromUploadedResponse(updatedImages);
    } catch (e) {
      setProcessingError(e);
    } finally {
      setProcessingImages(false);
    }
  };

  const updateFromUploadedResponse = updatedImages => {
    const newImages = images.map(stateImage => {
      const updated = updatedImages.find(updatedImage => updatedImage.id === stateImage.id);
      if (updated) {
        return updated;
      }
      return stateImage;
    });
    setImages(newImages);
  };

  const getSelectedImages = selectedIds => selectedIds
    .map(selected => images.find(image => image.id === selected))
    .filter(image => !!image);

  return {
    images,
    loading,
    error,
    loadPage,
    includeInWp,
    processingError,
    processingImages,
    getSelectedImages,
  };
};
