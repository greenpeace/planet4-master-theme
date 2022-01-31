const { __ } = wp.i18n;

import { Tooltip } from '@wordpress/components';

const COOKIES_DEFAULT_COPY = window.p4bk_vars.cookies_default_copy || {};

export const CookiesFieldResetButton = ({ fieldName, toAttribute, currentValue }) => {
  const defaultValue = COOKIES_DEFAULT_COPY[fieldName] || '';

  if (!currentValue || !defaultValue || currentValue === defaultValue) {
    return null;
  }

  return (
    <div className='field-reset-button'>
      <span
        className='cta'
        onClick={() => toAttribute(fieldName)(undefined)}
      >
        {__('Use default value', 'planet4-blocks-backend')}
      </span>
      <Tooltip text={__('This value is defined in the settings, in Planet 4 > Cookies', 'planet4-blocks-backend')}>
        <span className='info'>i</span>
      </Tooltip>
    </div>
  );
}
