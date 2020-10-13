const { __ } = wp.i18n;
import classNames from 'classnames';
import { useState, Fragment, useEffect } from '@wordpress/element';
import { useImages } from './archivePicker/useImages';
import { ArchivePickerList } from './archivePicker/ArchivePickerList';
import { SingleSidebar } from './archivePicker/SingleSidebar';
import { MultiSidebar } from './archivePicker/MultiSidebar';

const isNearScrollEnd = (event) => {
  const { scrollHeight, scrollTop, clientHeight } = event.target;
  const tillEnd = (scrollHeight - scrollTop - clientHeight) / scrollHeight;

  return tillEnd < 0.2;
};

const ArchivePicker = () => {
  const [searchText, setSearchText] = useState(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [enteredSearch, setEnteredSearch] = useState(null);
  const [selectedIds, setSelectedIds] = useState([]);

  const {
    loading,
    error,
    loadPage,
    getSelectedImages,
    images,
    processingError,
    processingImages,
    includeInWp,
  } = useImages();

  const isSelected = image => selectedIds.includes(image.id);

  const isOnlySelected = image => selectedIds.length === 1 && selectedIds.includes(image.id);

  const toggleSingleSelection = target => setSelectedIds(isOnlySelected(target) ? [] : [target.id]);

  const toggleMultiSelection = target => setSelectedIds(
    selectedIds.includes(target.id)
      ? selectedIds.filter(id => id !== target.id)
      : [...selectedIds, target.id]
  );

  useEffect(() => {
    loadPage(currentPage, searchText);
  }, [currentPage, searchText]);

  const selectedImages = getSelectedImages(selectedIds);

  return <Fragment>
    <form
      className={'archive-picker-search'}
      onSubmit={event => {
        event.preventDefault();
        if (!loading) {
          setSearchText(enteredSearch);
          setCurrentPage(1);
        }
      }}
      onChange={event => setEnteredSearch(event.target.value)}
    >
      <input
        type='text'
        disabled={loading}
      />
      <input type='submit' value={__('Search', 'planet4-master-theme-backend')} />
    </form>
    {loading && (
      <div className={'archive-picker-loading'}> loading...</div>
    )}
    {!!error && (
      <div>
        <h3>API error:</h3>
        <p> {error.message} </p>
      </div>
    )}
    <div className={'image-picker'}>
      <ul
        className={'picker-list'}
        onScroll={event => {
          if (!loading && isNearScrollEnd(event)) {
            setCurrentPage(currentPage + 1);
          }
        }}
      >
        <ArchivePickerList
          isSelected={isSelected}
          toggleMultiSelection={toggleMultiSelection}
          toggleSingleSelection={toggleSingleSelection}
          images={images}
        />
      </ul>
      {selectedImages.length > 0 && (
        <div className={classNames('picker-sidebar', { 'picker-sidebar-single': selectedImages.length === 1 })}>
          {selectedImages.length === 1 ?
            <SingleSidebar
              image={selectedImages[0]}
              processingError={processingError}
              processingImages={processingImages}
              includeInWp={includeInWp}
            /> :
            <MultiSidebar
              selectedImages={selectedImages}
              toggleMultiSelection={toggleMultiSelection}
            />
          }
        </div>
      )}
    </div>
  </Fragment>;
};

export default ArchivePicker;

