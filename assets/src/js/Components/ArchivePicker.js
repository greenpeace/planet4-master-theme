import {createContext, useContext, useEffect, useState, useCallback, useReducer, useMemo} from '@wordpress/element';
import {Spinner} from '@wordpress/components';
import classNames from 'classnames';
import ArchivePickerList from './archivePicker/ArchivePickerList';
import SingleSidebar from './archivePicker/SingleSidebar';
import MultiSidebar from './archivePicker/MultiSidebar';
import ArchivePickerToolbar from './archivePicker/ArchivePickerToolbar';

const {apiFetch, url, i18n} = wp;
const {addQueryArgs} = url;
const {__} = i18n;

const Context = createContext({});

const initialState = {
  loading: false,
  loaded: false,
  processingImages: false,
  processedImages: false,
  showAddedMessage: false,
  bulkSelect: false,
  images: [],
  selectedImages: {},
  selectedImagesAmount: 0,
  pageNumber: 1,
  searchText: [],
  error: null,
  errors: null,
};

const reducer = (state, action) => {
  switch (action.type) {
  case 'SEARCH':
    return {
      ...state,
      searchText: action.payload,
      ...{
        pageNumber:
          (JSON.stringify(action.payload) !== JSON.stringify(state.searchText)) ? 1 : state.pageNumber,
      },
    };
  case 'FETCH_IMAGES':
    return {
      ...state,
      loading: true,
      loaded: false,
    };
  case 'FETCHED_IMAGES':
    return {
      ...state,
      loading: false,
      loaded: true,
      images: action.payload.page > 1 ? state.images.concat(action.payload.images) : action.payload.images,
      pageNumber: action.payload.page,
    };
  case 'NEXT_PAGE':
    return {
      ...state,
      ...(!state.loading) ? {
        pageNumber: state.pageNumber + 1,
      } : {},
      loading: false,
      loaded: false,
    };
  case 'TOGGLE_IMAGE':
    let selectedImages = {...state.selectedImages};

    if (state.selectedImages[action.payload.image.id]) {
      selectedImages = {...state.selectedImages};
      delete selectedImages[action.payload.image.id];
    } else {
      const image = {[`${action.payload.image.id}`]: action.payload.image};
      selectedImages = {...(action.payload.multiSelection) ? {...state.selectedImages, ...image} : image};
    }

    return {
      ...state,
      selectedImages,
      selectedImagesAmount: Object.keys(selectedImages).length,
    };
  case 'PROCESS_IMAGES': {
    return {
      ...state,
      processingImages: true,
    };
  }
  case 'PROCESSED_IMAGES': {
    return {
      ...state,
      processingImages: false,
      showAddedMessage: true,
      images: state.images.map(stateImage => {
        const updated = action.payload.images.find(updatedImage => updatedImage.id === stateImage.id);
        if (updated) {
          return updated;
        }
        return stateImage;
      }),
      selectedImages: action.payload.images.map(img => img),
    };
  }
  case 'PROCESS_IMAGES_ERROR': {
    return {
      ...state,
      processedImages: true,
      processingImages: false,
      errors: {
        ...state.errors,
        ...{['PROCESS_IMAGES']: action.payload.error},
      },
    };
  }
  case 'HIDE_ADDED_MESSAGE': {
    return {
      ...state,
      showAddedMessage: false,
    };
  }
  case 'CLOSE_SIDEBAR': {
    return {
      ...state,
      selectedImages: {},
      selectedImagesAmount: 0,
    };
  }
  case 'ENABLE_BULK_SELECT': {
    return {
      ...state,
      bulkSelect: true,
    };
  }
  case 'CANCEL_BULK_SELECT': {
    return {
      ...state,
      selectedImages: {},
      selectedImagesAmount: 0,
      bulkSelect: false,
    };
  }
  case 'BULK_SELECT_ERROR': {
    return {
      ...state,
      error: action.payload,
    };
  }
  case 'REMOVE_ERROR': {
    const errors = {...state.errors};
    delete errors[action.payload.errorType];
    return {
      ...state,
      errors,
    }
  }
  case 'SET_ERROR': {
    return {
      ...state,
      loading: false,
      loaded: true,
      error: action.payload,
      errors: {
        ...state.errors,
        ...{[`${action.payload.type}`]: action.payload.error},
      },
    };
  }
  }
};

export default function ArchivePicker() {
  const [{loading, loaded, showAddedMessage, processingImages, /*processingError,*/ images, pageNumber, searchText, selectedImages, selectedImagesAmount, bulkSelect, error, errors}, dispatch] = useReducer(reducer, initialState);
  const [abortController, setAbortController] = useState(null);

  const fetch = useCallback(async () => {
    dispatch({type: 'FETCH_IMAGES'});

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

      dispatch({type: 'FETCHED_IMAGES', payload: {
        images: result,
        page: pageNumber,
      }});
    } catch (err) {
      if (err.name !== 'AbortError') {
        dispatch({type: 'SET_ERROR', payload: {
          type: 'FETCH_IMAGES',
          error: err.message,
        }});
      }
    }
  }, [loading, loaded, images, error, searchText, pageNumber, dispatch]);

  const includeInWp = async (ids = []) => {
    // try {
      dispatch({type: 'PROCESS_IMAGES'});

      Promise.all(ids.map((id) => {
        return apiFetch({
          method: 'POST',
          path: '/planet4/v1/image-archive/transfer',
          data: {
            ids,
            use_original_language: false,
          },
        });
      })).then(value => {
        console.log(value);
        dispatch({type: 'PROCESSED_IMAGES', payload: {images: updatedImages}});
      }).catch((err) => {
        console.log(err);
        throw new Error(err.message);
      });

      // console.log(updatedImages);

      // const updatedImages = await apiFetch({
      //   method: 'POST',
      //   path: '/planet4/v1/image-archive/transfer',
      //   data: {
      //     ids,
      //     use_original_language: false,
      //   },
      // });
      // dispatch({type: 'PROCESSED_IMAGES', payload: {images: updatedImages}});
    // } catch (err) {
      // dispatch({type: 'PROCESS_IMAGES_ERROR', payload: {error: err}});
      // dispatch({type: 'SET_ERROR', payload: {
      //   error: {
      //     type: 'PROCESS_IMAGES',
      //     errorMessage: err.message,
      //   },
      // }});
    // }
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
    if (showAddedMessage) {
      setTimeout(() => {
        dispatch({type: 'HIDE_ADDED_MESSAGE'});
      }, 5000);
    }
  }, [showAddedMessage]);

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
        // processingError,
        processingImages,
        showAddedMessage,
        includeInWp,
        selectedImages,
        selectedImagesAmount,
        searchText,
        dispatch,
      }}
    >
      <ArchivePickerToolbar />

      {!!error && error.type === 'FETCH_IMAGES' && (
        <div>
          <h3>API error:</h3>
          <div dangerouslySetInnerHTML={{__html: error.errorMessage}} />
        </div>
      )}

      <div className={classNames('image-picker', {'open-sidebar': selectedImagesAmount > 0 && !bulkSelect})}>
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

        {(selectedImagesAmount > 0 && !bulkSelect) ? (
          <div className={'picker-sidebar'}>
            {selectedImagesAmount === 1 ? <SingleSidebar /> : <MultiSidebar />}
          </div>) : null}
      </div>
    </Context.Provider>
  ), [
    loading,
    loaded,
    bulkSelect,
    error,
    errors,
    images,
    // processingError,
    processingImages,
    showAddedMessage,
    searchText,
    selectedImages,
    selectedImagesAmount,
    dispatch,
    fetch,
    includeInWp,
  ]);
}

export const useArchivePickerContext = () => useContext(Context);
