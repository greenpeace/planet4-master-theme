import {URLValidationMessage} from '../URLValidationMessage/URLValidationMessage';

const {TextControl} = wp.components;

export const URLInput = props => {
  const {label, placeholder, value, onChange, disabled, help} = props;

  return (
    <div>
      <TextControl
        __nextHasNoMarginBottom
        __next40pxDefaultSize
        label={label}
        placeholder={placeholder}
        value={value}
        onChange={onChange}
        disabled={disabled}
        help={help}
      />
      {!disabled &&
            <URLValidationMessage
              url={value}
            />
      }
    </div>
  );
};
