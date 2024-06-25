import {useRef, useState, useEffect, useCallback, useMemo} from '@wordpress/element';
import {Spinner} from '@wordpress/components';
import classNames from 'classnames';
import {ACTIONS, useArchivePickerContext} from './ArchivePicker';

const {__} = wp.i18n;
const MAX_SEARCHES = 8;

export default function MultiSearchOption() {
  const [localSearchText, setLocalSearchText] = useState([]);
  const [disableSearchButton, setDisableSearchButton] = useState(true);
  const inputRef = useRef();
  const {
    loading,
    loaded,
    searchText,
    dispatch,
  } = useArchivePickerContext();

  const clearList = () => {
    setLocalSearchText([]);
  };

  const addItem = useCallback(evt => {
    if (loading || !(localSearchText.length < MAX_SEARCHES)) {
      return;
    }

    const value = evt.currentTarget.value !== '' ? evt.currentTarget.value.split(',') : '';

    if (evt.currentTarget.value !== '') {
      setDisableSearchButton(false);
    } else {
      setDisableSearchButton(true);
    }

    if (evt.keyCode === 13 || evt.keyCode === 188 || evt.currentTarget.value.includes(',')) {
      for (const item in value) {
        if (item <= MAX_SEARCHES - 1 && value[item] !== '' && !localSearchText.find(_ => _ === value[item])) {
          setLocalSearchText(list => [...list].concat(value[item]));
        }
      }
      evt.currentTarget.value = '';
    }
  }, [loading, localSearchText]);

  const removeItem = useCallback(item => {
    if (loading) {
      return;
    }
    setLocalSearchText(list => list.filter(itemValue => itemValue !== item));
  }, [loading]);

  const onSubmitHandler = useCallback(async evt => {
    evt.preventDefault();
    setDisableSearchButton(true);

    let tmpLocalSearchText = localSearchText;

    if (inputRef.current && inputRef.current.value !== '') {
      tmpLocalSearchText = tmpLocalSearchText.concat(inputRef.current.value);
      inputRef.current.value = '';
    }

    if (tmpLocalSearchText.length) {
      setLocalSearchText(tmpLocalSearchText);
    }
  }, [localSearchText, setDisableSearchButton]);

  useEffect(() => {
    if (searchText.toString() !== localSearchText.toString()) {
      setDisableSearchButton(true);
      dispatch({type: ACTIONS.SEARCH, payload: localSearchText});
    }
  }, [localSearchText]);

  return useMemo(() => (
    <form className="multiple-search-form" onSubmit={evt => evt.preventDefault()}>
      <div className="multiple-search">
        <div className={`multiple-search-wrapper-input ${classNames({disabled: !(localSearchText.length < MAX_SEARCHES)})}`}>
          <input
            ref={inputRef}
            type="text"
            onKeyUp={addItem}
            onChange={addItem}
            placeholder={__('Search', 'planet4-master-theme-backend')}
          />
        </div>

        {searchText.length ? (
          <ul className="multiple-search-list">
            {
              searchText.map(item => (
                <li key={item} className="multiple-search-item">
                  {item}
                  <span role="button" className="delete-icon" aria-hidden="true" tabIndex={0} onClick={() => removeItem(item)} />
                </li>
              ))
            }
          </ul>
        ) : null}
      </div>

      <nav className="multiple-search-nav">
        <button
          disabled={!searchText.length}
          type="button"
          className="button"
          onClick={clearList}
        >{__('Clear all', 'planet4-master-theme-backend')}</button>

        <button
          disabled={disableSearchButton}
          onClick={onSubmitHandler}
          type="button"
          className="button"
        >{__('Search', 'planet4-master-theme-backend')}</button>
      </nav>
      {!!searchText.length && loading && <Spinner />}
    </form>
  ), [
    loading,
    loaded,
    disableSearchButton,
    localSearchText,
    searchText,
    setLocalSearchText,
    onSubmitHandler,
    addItem,
  ]);
}
