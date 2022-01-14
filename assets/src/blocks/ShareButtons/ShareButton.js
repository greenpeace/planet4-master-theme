const { __ } = wp.i18n;

export const ShareButton = ({
  href = '',
  providerName = '',
  icon = '',
  hiddenText = '',
  gaCategory,
  gaAction,
  gaLabel,
  openInNewTab,
}) => (
  <a href={href}
    className={`social-share-button ${providerName}`}
    data-ga-event='uaevent'
    data-ga-event-category='Social Share'
    data-ga-category={gaCategory}
    data-ga-action={gaAction}
    data-ga-label={gaLabel}
    { ...openInNewTab && { target: '_blank' } }
  >
    <svg viewBox="0 0 32 32" className='icon'>
      <use xlinkHref={ `${window.p4bk_vars.themeUrl}/assets/build/sprite.symbol.svg#${icon}` } />
    </svg>
    <span className="visually-hidden">{__( hiddenText, 'planet4-master-theme' )}</span>
  </a>
);
