import { SvgIcon } from "../SvgIcon";
import { parseUtmParams } from '../utils';

const { __ } = wp.i18n;

export const FacebookShareFrontend = ({
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
}) => (
  <a href={ `${baseSharedUrl}?u=${encodeURIComponent(url)}&${parseUtmParams('facebook', utmMedium, utmContent, utmCampaign)}` }
    className="social-share-button facebook"
    data-ga-event={gaEvent}
    data-ga-event-category={gaEventCategory}
    data-ga-category={gaCategory}
    data-ga-action={gaAction}
    data-ga-label={gaLabel}
    { ...openInNewTab && { target: '_blank' } }
  >
    <SvgIcon {...{name: "facebook-f"}} />
    <span className="visually-hidden">{__( 'Share on', 'planet4-master-theme' )} Facebook</span>
  </a>
);
