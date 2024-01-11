import {memo, useMemo} from '@wordpress/element';
import {compose} from '@wordpress/compose';
import {withSelect} from '@wordpress/data';
import {FormTokenField} from '@wordpress/components';
import {useSelector} from '../../functions/useSelector';

const TagSelector = memo(props => {
  const {suggestions, onChange, label, placeholder, value, ...ownProps} = props;
  const [parsedSuggestions, parsedValue, handleChange] = useSelector(suggestions, value, onChange);

  return useMemo(() => (
    <FormTokenField
      suggestions={parsedSuggestions}
      label={label || 'Select Tags'}
      onChange={handleChange}
      placeholder={placeholder || 'Select Tags'}
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

export default compose(
  withSelect(select => ({
    suggestions: select('core').getEntityRecords('taxonomy', 'post_tag', {hide_empty: false, per_page: -1}) || [],
  }))
)(TagSelector);
