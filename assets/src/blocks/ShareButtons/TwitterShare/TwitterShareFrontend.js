import { SvgIcon } from "../SvgIcon";
import { parseUtmParams } from '../utils';

const { __ } = wp.i18n;

const parseUrl = (url, title, description, account) => {
  return `?url=${encodeURIComponent(url)}`
    + `&text=${encodeURIComponent(title)}`
    + (description ? ` - ${encodeURIComponent(description)}` : '')
    + (account ? ` via @${encodeURIComponent(account)}&related=${encodeURIComponent(account)}` : '');
}

export const TwitterShareFrontend = ({
  url,
  utmMedium,
  utmContent,
  utmCampaign,
  gaEvent,
  gaEventCategory,
  gaCategory,
  gaAction,
  gaLabel,
  openInNewTab,
  baseSharedUrl,
  text,
  description,
  account,
}) => (
  <a
    href={`${baseSharedUrl}${parseUrl(url, text, description, account)}&${parseUtmParams('facebook', utmMedium, utmContent, utmCampaign)}`}
    className="social-share-button twitter"
    data-ga-event={gaEvent}
    data-ga-event-category={gaEventCategory}
    data-ga-category={gaCategory}
    data-ga-action={gaAction}
    data-ga-label={gaLabel}
    { ...openInNewTab && { target: '_blank' } }
  >
    <SvgIcon {...{name: "twitter"}} />
    <span className="visually-hidden">{__( 'Share on', 'planet4-master-theme' )} Twitter</span>
  </a>
);
