import {memo, useMemo} from '@wordpress/element';
import {FormTokenField} from '@wordpress/components';
import {useSelector} from '../../functions/useSelector';

export const TaxonomySelector = memo(props => {
  const {suggestions, onChange, label, placeholder, value, ...ownProps} = props;
  const [parsedSuggestions, parsedValue, handleChange] = useSelector(suggestions, value, onChange);

  return useMemo(() => (
    <FormTokenField
      __nextHasNoMarginBottom
      __next40pxDefaultSize
      suggestions={parsedSuggestions}
      label={label}
      onChange={handleChange}
      placeholder={placeholder}
      value={parsedValue}
      {...ownProps}
    />
  ), [
    label,
    handleChange,
    ownProps,
    placeholder,
    parsedSuggestions,
    parsedValue,
  ]);
});
