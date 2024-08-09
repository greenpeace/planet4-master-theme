import classNames from 'classnames';
import ArchivePickerList from './ArchivePickerList';
import SingleSidebar from './SingleSidebar';
import MultiSidebar from './MultiSidebar';
import ArchivePickerToolbar from './ArchivePickerToolbar';
import {
  updateImageBlockAttributes,
  updateHappyPointAttributes,
  updateMediaAndTextAttributes,
  updateCarouselBlockAttributes,
} from './blockUpdateFunctions';

const {Spinner} = wp.components;
const {createContext, useContext, useEffect, useState, useCallback, useReducer, useMemo} = wp.element;
const {apiFetch, url, i18n} = wp;
const {addQueryArgs} = url;
const {__} = i18n;

const timeout = delay => {
  return new Promise(resolve => setTimeout(resolve, delay));
};

const acceptedBlockTypes = ['core/image', 'planet4-blocks/happypoint', 'core/media-text', 'planet4-blocks/carousel-header'];

export const EDITOR_VIEW = 'editor';
export const ADMIN_VIEW = 'admin';

export const ACTIONS = {
  CLOSE_SIDEBAR: 'closeSidebar',
  BULK_SELECT_ENABLE: 'bulkSelectEnable',
  BULK_SELECT_CANCEL: 'bulkSelectCancel',
  BULK_SELECT_ERROR: 'bulkSelectError',
  DESELECT_IMAGE: 'deselectImage',
  SELECT_IMAGES: 'selectImages',
  FETCH_IMAGES: 'fetchImages',
  FETCHED_IMAGES: 'fetchedImages',
  NEXT_PAGE: 'nextPage',
  PROCESS_IMAGES: 'processImages',
  PROCESS_IMAGES_ERROR: 'processedImagesError',
  PROCESSED_IMAGES: 'processedImages',
  REMOVE_ERROR: 'removeError',
  SEARCH: 'search',
  SET_ERROR: 'setError',
  ADD_IMAGE_TO_POST: 'addImageToPost',
  ADD_IMAGE_TO_POST_ERROR: 'addImageToPostError',
  ADDED_IMAGE_TO_POST: 'addedImageToPost',
  SET_CURRENT_BLOCK_IMAGE_ID: 'setCurrentBlockImageId',
};

const Context = createContext({});

const initialState = {
  loading: false,
  loaded: false,
  processing: false,
  bulkSelect: false,
  images: [],
  selectedImages: [],
  processingIds: [],
  processedIds: [],
  pageNumber: 1,
  searchText: [],
  error: null,
  errors: null,
  imageAdded: false,
  currentBlockImageId: null,
};

const reducer = (state, action) => {
  switch (action.type) {
  case ACTIONS.SEARCH: {
    return {
      ...state,
      searchText: action.payload,
      ...{
        pageNumber:
            (JSON.stringify(action.payload) !== JSON.stringify(state.searchText)) ? 1 : state.pageNumber,
      },
    };
  }
  case ACTIONS.FETCH_IMAGES: {
    return {
      ...state,
      loading: true,
      loaded: false,
    };
  }
  case ACTIONS.FETCHED_IMAGES: {
    return {
      ...state,
      loading: false,
      loaded: true,
      images: action.payload.page > 1 ? state.images.concat(action.payload.images) : action.payload.images,
      pageNumber: action.payload.page,
    };
  }
  case ACTIONS.SELECT_IMAGES: {
    return {
      ...state,
      selectedImages: [...action.payload.selection],
    };
  }
  case ACTIONS.DESELECT_IMAGE: {
    return {
      ...state,
      selectedImages: state.selectedImages.filter(image => image.id !== action.payload.selection.id),
    };
  }
  case ACTIONS.PROCESS_IMAGES: {
    return {
      ...state,
      processing: true,
      processingIds: [...state.processingIds, ...action.payload.selection].flat(),
    };
  }
  case ACTIONS.PROCESSED_IMAGES: {
    const processedIds = [...state.processedIds, action.payload.images.map(img => img.id)].flat();
    const images = state.images.map(img => {
      const updated = action.payload.images.find(updatedImage => updatedImage.id === img.id);
      return updated ? updated : img;
    });

    return {
      ...state,
      images,
      selectedImages: state.selectedImages.map(img => {
        const updated = images.find(_ => _.id === img.id);
        return updated ? updated : img;
      }),
      processing: false,
      processedIds,
      processingIds: state.processingIds.filter(id => !processedIds.includes(id)),
    };
  }
  case ACTIONS.PROCESS_IMAGES_ERROR: {
    return {
      ...state,
      errors: {
        ...state.errors,
        ...{[ACTIONS.PROCESS_IMAGES]: action.payload.error},
      },
    };
  }
  case ACTIONS.CLOSE_SIDEBAR: {
    return {
      ...state,
      imageAdded: false,
      selectedImages: [],
    };
  }
  case ACTIONS.BULK_SELECT_ENABLE: {
    return {
      ...state,
      bulkSelect: true,
    };
  }
  case ACTIONS.BULK_SELECT_CANCEL: {
    return {
      ...state,
      selectedImages: [],
      bulkSelect: false,
      processing: false,
    };
  }
  case ACTIONS.BULK_SELECT_ERROR: {
    return {
      ...state,
      error: action.payload,
    };
  }
  case ACTIONS.REMOVE_ERROR: {
    const errors = {...state.errors};
    delete errors[action.payload.errorType];
    return {
      ...state,
      errors,
    };
  }
  case ACTIONS.SET_ERROR: {
    return {
      ...state,
      loading: false,
      loaded: true,
      errors: {
        ...state.errors,
        ...{[`${action.payload.type}`]: {
          message: action.payload.error,
        }},
      },
    };
  }
  case ACTIONS.NEXT_PAGE: {
    return {
      ...state,
      ...(!state.loading) ? {
        pageNumber: state.pageNumber + 1,
      } : {},
      loading: false,
      loaded: false,
    };
  }
  case ACTIONS.ADD_IMAGE_TO_POST: {
    return {
      ...state,
      processing: true,
    };
  }
  case ACTIONS.ADDED_IMAGE_TO_POST: {
    return {
      ...state,
      processing: false,
      imageAdded: true,
    };
  }
  case ACTIONS.ADD_IMAGE_TO_POST_ERROR: {
    return {
      ...state,
      error: action.payload,
    };
  }
  case ACTIONS.SET_CURRENT_BLOCK_IMAGE_ID: {
    return {
      ...state,
      currentBlockImageId: action.payload,
    };
  }
  default: {
    return {
      ...state,
    };
  }
  }
};

export default function ArchivePicker({view = ADMIN_VIEW}) {
  const [{
    loading,
    loaded,
    processing,
    processingImages,
    processedImages,
    processingIds,
    processedIds,
    images,
    pageNumber,
    searchText,
    selectedImages,
    bulkSelect,
    error,
    errors,
    imageAdded,
    currentBlockImageId,
  }, dispatch] = useReducer(reducer, initialState);
  const [abortController, setAbortController] = useState(null);
  const [selectedImagesIds, setSelectedImagesIds] = useState([]);

  const fetch = useCallback(async () => {
    dispatch({type: ACTIONS.FETCH_IMAGES});

    try {
      const result = await apiFetch({
        path: addQueryArgs('/planet4/v1/image-archive/fetch', {
          page: pageNumber,
          ...(searchText.length) ? {
            search_text: searchText,
          } : {},
        }),
        signal: abortController?.signal,
      });

      dispatch({type: ACTIONS.FETCHED_IMAGES, payload: {
        images: result,
        page: pageNumber,
      }});
    } catch (err) {
      if (err.name !== 'AbortError') {
        dispatch({type: 'SET_ERROR', payload: {
          type: ACTIONS.FETCH_IMAGES,
          error: err.message,
        }});
      }
    }
  }, [loading, loaded, images, error, searchText, pageNumber, dispatch]);

  const currentBlock = wp.data.select('core/block-editor').getSelectedBlock();

  const getImageDetails = useCallback(async id => {
    try {
      // On first try wp returns undefined, so need to make call again to get image details.
      for (let retries = 0; retries < 2; ++retries) {
        const newImageUploaded = await wp.data.select('core').getMedia(id);
        if (newImageUploaded) {
          return newImageUploaded;
        }

        await timeout(3000);
      }
    } catch (err) {
      dispatch({type: ACTIONS.ADD_IMAGE_TO_POST_ERROR, payload: err});
    }
  }, []);

  const processImageForBlock = async (imageID, updateAttributeFunction) => {
    const uploadedImage = await getImageDetails(imageID);
    const updatedAttributes = updateAttributeFunction(uploadedImage, currentBlock);
    await wp.data.dispatch('core/block-editor').updateBlock(currentBlock.clientId, updatedAttributes);
  };

  const processImageToAddToEditor = async id => {
    dispatch({type: ACTIONS.ADD_IMAGE_TO_POST});
    try {
      if (currentBlock.name === 'core/image') {
        await processImageForBlock(id, updateImageBlockAttributes);
      } else if (currentBlock.name === 'planet4-blocks/carousel-header') {
        await processImageForBlock(id, updateCarouselBlockAttributes);
      } else if (currentBlock.name === 'core/media-text') {
        await processImageForBlock(id, updateMediaAndTextAttributes);
      } else {
        // Happypoint Block
        const updatedAttributes = updateHappyPointAttributes(id);
        await wp.data.dispatch('core/block-editor').updateBlock(currentBlock.clientId, updatedAttributes);
      }

      dispatch({type: ACTIONS.ADDED_IMAGE_TO_POST});
      await timeout(1000);
      dispatch({type: ACTIONS.CLOSE_SIDEBAR});
      document.querySelector('.media-modal-close').click();
    } catch (err) {
      dispatch({type: ACTIONS.ADD_IMAGE_TO_POST_ERROR, payload: err});
    }
  };

  const includeInWp = async (ids = [], viewProp) => {
    dispatch({type: ACTIONS.PROCESS_IMAGES, payload: {selection: ids}});

    Promise.all(ids.map(id => {
      return apiFetch({
        method: 'POST',
        path: '/planet4/v1/image-archive/transfer',
        data: {
          ids: [id],
          use_original_language: false,
        },
      });
    })).then(result => {
      const payload = result.flat();
      if (viewProp === EDITOR_VIEW) {
        processImageToAddToEditor(payload[0].wordpress_id);
      }
      dispatch({type: ACTIONS.PROCESSED_IMAGES, payload: {images: payload}});
    }).catch(err => {
      dispatch({type: 'SET_ERROR', payload: {
        type: ACTIONS.PROCESS_IMAGES,
        error: err.message,
      }});
    });
  };

  useEffect(() => {
    if (loaded) {
      setAbortController(null);
    }
  }, [loaded]);

  useEffect(() => {
    if (abortController) {
      fetch();
    }

    return () => {
      if (abortController) {
        abortController.abort();
      }
    };
  }, [abortController]);

  useEffect(() => {
    if (abortController) {
      abortController.abort();
    }

    if (!loading) {
      setAbortController(new AbortController());
    }
  }, [searchText]);

  useEffect(() => {
    if (pageNumber > 1 && !loading && !loaded) {
      setAbortController(new AbortController());
    }
  }, [pageNumber]);

  useEffect(() => {
    setSelectedImagesIds(selectedImages.map(image => image.id));
  }, [selectedImages]);

  useEffect(() => {
    if (currentBlock) {
      dispatch({type: ACTIONS.SET_CURRENT_BLOCK_IMAGE_ID, payload: currentBlock.attributes.id});
    }
  }, [currentBlock]);

  return useMemo(() => (
    <Context.Provider
      value={{
        loading,
        loaded,
        bulkSelect,
        error,
        errors,
        fetch,
        images,
        processing,
        processingImages,
        processedImages,
        includeInWp,
        processImageToAddToEditor,
        selectedImages,
        selectedImagesIds,
        searchText,
        processingIds,
        processedIds,
        dispatch,
        imageAdded,
        currentBlockImageId,
        view,
      }}
    >
      {view === ADMIN_VIEW && (
        <section className="archive-picker">
          <div className={classNames('archive-picker-main', {'is-open': selectedImages.length > 0 && !bulkSelect})}>
            <ArchivePickerToolbar />

            {!!errors && errors[ACTIONS.FETCH_IMAGES] && (
              <div>
                <h3>API error:</h3>
                <div dangerouslySetInnerHTML={{__html: errors[ACTIONS.FETCH_IMAGES].message}} />
              </div>
            )}

            <ArchivePickerList />

            {!!images.length && (
              <div className="help">
                <div
                  className="tooltip"
                  dangerouslySetInnerHTML={{
                    __html: __(
                      'The <strong>Greenpeace Media</strong> pulls images from <a target="_blank" href="https://media.greenpeace.org/">media.greenpeace.org</a>. You can import these images into your Wordpress Media Library and Post/Page. If you have further questions, you can visit the <a target="_blank" href="https://planet4.greenpeace.org/manage/administer/media-archive/">Greenpeace Media Page</a> in the Handbook.', 'planet4-master-theme-backend'
                    ),
                  }}
                />
                <span>?</span>
              </div>
            )}

            {loading && (
              <div className="archive-picker-loading"><Spinner /></div>
            )}
          </div>

          <div className={classNames('archive-picker-sidebar', {'archive-picker-sidebar-open': selectedImages.length > 0 && !bulkSelect})}>
            <div className="picker-sidebar">
              {selectedImages.length === 1 ? <SingleSidebar image={selectedImages[0]} /> : <MultiSidebar />}
            </div>
          </div>
        </section>
      )}

      {view === EDITOR_VIEW && (
        <>
          {(acceptedBlockTypes.includes(currentBlock.name)) ? (
            <section className="archive-picker">
              <div className={classNames('archive-picker-main', {'is-open': selectedImages.length > 0 && !bulkSelect})}>
                <ArchivePickerToolbar />

                {!!errors && errors[ACTIONS.FETCH_IMAGES] && (
                  <div>
                    <h3>API error:</h3>
                    <div dangerouslySetInnerHTML={{__html: errors[ACTIONS.FETCH_IMAGES].message}} />
                  </div>
                )}

                <ArchivePickerList />

                {!!images.length && (
                  <div className="media-archive-help">
                    <div
                      className="tooltip"
                      dangerouslySetInnerHTML={{
                        __html: __(
                          'The <strong>Greenpeace Media</strong> pulls images from <a target="_blank" href="https://media.greenpeace.org/">media.greenpeace.org</a>. You can import these images into your Wordpress Media Library and Post/Page. If you have further questions, you can visit the <a target="_blank" href="https://planet4.greenpeace.org/manage/administer/media-archive/">Greenpeace Media Page</a> in the Handbook.', 'planet4-master-theme-backend'
                        ),
                      }}
                    />
                    <span>?</span>
                  </div>
                )}

                {loading && (
                  <div className="archive-picker-loading"><Spinner /></div>
                )}
              </div>

              <div className={classNames('archive-picker-sidebar', {'archive-picker-sidebar-open': selectedImages.length > 0 && !bulkSelect})}>
                <div className="picker-sidebar">
                  {selectedImages.length === 1 ? <SingleSidebar image={selectedImages[0]} /> : <MultiSidebar />}
                </div>
              </div>
            </section>
          ) : (
            <p className="media-archive-info">{__('Use Media Library Tab for this Block!', 'planet4-master-theme-backend')}</p>
          )}
        </>
      )}
    </Context.Provider>
  ), [
    loading,
    loaded,
    bulkSelect,
    error,
    errors,
    images,
    processing,
    processingImages,
    processedImages,
    processingIds,
    processedIds,
    searchText,
    selectedImages,
    selectedImagesIds,
    dispatch,
    fetch,
    includeInWp,
    processImageToAddToEditor,
    imageAdded,
    currentBlockImageId,
    view,
  ]);
}

export const useArchivePickerContext = () => useContext(Context);
