import {createContext, useContext, useEffect, useState, useCallback, useReducer, useMemo} from '@wordpress/element';
import classNames from 'classnames';
import ArchivePickerList from './archivePicker/ArchivePickerList';
import SingleSidebar from './archivePicker/SingleSidebar';
import MultiSidebar from './archivePicker/MultiSidebar';
import MultiSearchOption from './archivePicker/MultiSearchOption';

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
  images: [],
  selectedImages: {},
  selectedImagesAmount: 0,
  pageNumber: 1,
  searchText: [],
  error: null,
  processingError: null,
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
  case 'PROCESSING_IMAGES': {
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
  case 'PROCESSING_ERROR': {
    return {
      ...state,
      processingError: action.payload.error,
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
  case 'SET_ERROR': {
    return {
      ...state,
      loading: false,
      loaded: true,
      error: action.payload,
    };
  }
  }
};

export default function ArchivePicker() {
  const [{
    loading,
    loaded,
    showAddedMessage,
    processingImages,
    processingError,
    images,
    pageNumber,
    searchText,
    selectedImages,
    selectedImagesAmount,
    error,
  }, dispatch] = useReducer(reducer, initialState);
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
        dispatch({type: 'SET_ERROR', payload: err});
      }
    }
  }, [loading, loaded, images, error, searchText, pageNumber, dispatch]);

  const includeInWp = async id => {
    try {
      dispatch({type: 'PROCESSING_IMAGES'});

      const updatedImages = await apiFetch({
        method: 'POST',
        path: '/planet4/v1/image-archive/transfer',
        data: {
          ids: [id],
          use_original_language: false,
        },
      });
      dispatch({type: 'PROCESSED_IMAGES', payload: {images: updatedImages}});
    } catch (err) {
      dispatch({type: 'PROCESSING_ERROR', payload: {error: err}});
    }
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
        error,
        fetch,
        images,
        processingError,
        processingImages,
        showAddedMessage,
        includeInWp,
        selectedImages,
        selectedImagesAmount,
        searchText,
        dispatch,
      }}
    >
      <MultiSearchOption />

      {!!error && (
        <div>
          <h3>API error:</h3>
          <p> {error.message} </p>
        </div>
      )}

      <div className={classNames('image-picker', {'open-sidebar': selectedImagesAmount > 0})}>
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
          <div className="archive-picker-loading">{__('Loading...', 'planet4-master-theme-backend')}</div>
        )}

        {selectedImagesAmount > 0 ? (
          <div className={'picker-sidebar'}>
            {selectedImagesAmount === 1 ? <SingleSidebar /> : <MultiSidebar />}
          </div>) : null}
      </div>
    </Context.Provider>
  ), [
    loading,
    loaded,
    error,
    images,
    processingError,
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
