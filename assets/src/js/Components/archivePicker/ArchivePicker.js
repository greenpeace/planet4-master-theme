import {createContext, useContext, useEffect, useState, useCallback, useReducer, useMemo} from '@wordpress/element';
import {Spinner} from '@wordpress/components';
import classNames from 'classnames';
import ArchivePickerList from './ArchivePickerList';
import SingleSidebar from './SingleSidebar';
import MultiSidebar from './MultiSidebar';
import ArchivePickerToolbar from './ArchivePickerToolbar';

const {apiFetch, url, i18n} = wp;
const {addQueryArgs} = url;
const {__} = i18n;

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
};

const Context = createContext({});

const initialState = {
  loading: false,
  loaded: false,
  bulkSelect: false,
  images: [],
  selectedImages: [],
  processingIds: [],
  processedIds: [],
  pageNumber: 1,
  searchText: [],
  error: null,
  errors: null,
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
  default: {
    return {
      ...state,
    };
  }
  }
};

export default function ArchivePicker() {
  const [{
    loading,
    loaded,
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

  const includeInWp = async (ids = []) => {
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
        processingImages,
        processedImages,
        includeInWp,
        selectedImages,
        selectedImagesIds,
        searchText,
        processingIds,
        processedIds,
        dispatch,
      }}
    >
      <section className={classNames('archive-picker', {'open-sidebar': selectedImages.length > 0 && !bulkSelect})}>
        <ArchivePickerToolbar />

        {!!errors && errors[ACTIONS.FETCH_IMAGES] && (
          <div>
            <h3>API error:</h3>
            <div dangerouslySetInnerHTML={{__html: errors[ACTIONS.FETCH_IMAGES].message}} />
          </div>
        )}

        <div className={classNames('image-picker', {'open-sidebar': selectedImages.length > 0 && !bulkSelect})}>
          <ArchivePickerList />

          {!!images.length && (
            <div className="help">
              <div
                className="tooltip"
                dangerouslySetInnerHTML={{
                  __html: __(
                    'The <strong>Media Archive</strong> pulls images from <a target="_blank" href="https://media.greenpeace.org/">media.greenpeace.org</a>. You can import these images into your Wordpress Media Library and Post/Page. If you have further questions, you can visit the <a target="_blank" href="https://planet4.greenpeace.org/manage/administer/media-archive/">Media Archive Page</a> in the Handbook.', 'planet4-master-theme-backend'
                  ),
                }}
              />
              <span>?</span>
            </div>
          )}

          {loading && (
            <div className="archive-picker-loading"><Spinner /></div>
          )}

          {(selectedImages.length && !bulkSelect) ? (
            <div className="picker-sidebar">
              {selectedImages.length === 1 ? <SingleSidebar image={selectedImages[0]} /> : <MultiSidebar />}
            </div>) : null}
        </div>
      </section>
    </Context.Provider>
  ), [
    loading,
    loaded,
    bulkSelect,
    error,
    errors,
    images,
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
  ]);
}

export const useArchivePickerContext = () => useContext(Context);
