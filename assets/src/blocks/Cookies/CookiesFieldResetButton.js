const {__} = wp.i18n;
const {Tooltip} = wp.components;

const COOKIES_DEFAULT_COPY = window.p4_vars.options.cookies_default_copy || {};

export const CookiesFieldResetButton = ({fieldName, toAttribute, currentValue}) => {
  const defaultValue = COOKIES_DEFAULT_COPY[fieldName] || '';

  if (!currentValue || !defaultValue || currentValue === defaultValue) {
    return null;
  }

  return (
    <div className="field-reset-button">
      <Tooltip text={__('This value is defined in the settings, in Planet 4 > Cookies', 'planet4-blocks-backend')}>
        <span className="info">i</span>
      </Tooltip>
      <span
        className="cta"
        onClick={() => toAttribute(fieldName)(undefined)}
        role="presentation"
      >
        {__('Use default value', 'planet4-blocks-backend')}
      </span>
    </div>
  );
};
