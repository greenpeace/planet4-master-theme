import {useCallback, useEffect, useState} from '@wordpress/element';

/**
 * This function clean up list with potentially including undefined values
 *
 * @param {Array} list
 * @return {Array} A new list excluding 'undefined' values
 */
const cleanUpList = list => list.filter(item => item !== undefined);

/**
 * This hook parse tags, post-types from form token field
 *
 * @param {Array}    suggestions list of suggestions (tags, post-types, etc.)
 * @param {Array}    value       receive a list of IDs
 * @param {Function} onChange    returns all the IDs to search
 * @return {Array} A list with parsed suggestions, parsed values and the common handleChange function
 */
export const useSelector = (
  suggestions,
  value,
  onChange
) => {
  const [parsedSuggestions, setParsedSuggestions] = useState([]);
  const [parsedValue, setParsedValue] = useState([]);

  const handleChange = useCallback(searchText => {
    onChange(
      cleanUpList(
        searchText.map(text => suggestions.find(item => item.name === text)?.id)
      )
    );
  }, [suggestions, onChange]);

  useEffect(() => {
    setParsedValue(
      cleanUpList(value.map(id => suggestions.find(item => item.id === id)?.name))
    );
  }, [suggestions, value]);

  useEffect(() => {
    if (suggestions.length) {
      setParsedSuggestions(suggestions.map(item => item.name));
    }
  }, [suggestions]);

  return [
    parsedSuggestions,
    parsedValue,
    handleChange,
  ];
};
