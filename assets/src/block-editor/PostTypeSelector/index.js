import {memo, useMemo} from '@wordpress/element';
import {compose} from '@wordpress/compose';
import {withSelect} from '@wordpress/data';
import {FormTokenField} from '@wordpress/components';
import {useSelector} from '../../functions/useSelector';

const PostTypeSelector = memo(props => {
  const {suggestions, onChange, label, placeholder, value, ...ownProps} = props;
  const [parsedSuggestions, parsedValue, handleChange] = useSelector(suggestions, value, onChange);

  return useMemo(() => (
    <FormTokenField
      suggestions={parsedSuggestions}
      label={label || 'Select Post Types'}
      onChange={handleChange}
      placeholder={placeholder || 'Select Post Types'}
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
    suggestions: select('core').getEntityRecords('taxonomy', 'p4-page-type') || [],
  }))
)(PostTypeSelector);
