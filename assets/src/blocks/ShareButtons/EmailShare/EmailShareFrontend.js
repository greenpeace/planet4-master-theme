import { SvgIcon } from "../SvgIcon";
import { parseUtmParams } from '../utils';

const { __ } = wp.i18n;

export const EmailShareFrontend = ({
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
  title,
  body,
}) => (
  <a href={ `mailto:?subject=${title}&body=${body ? encodeURIComponent(body) : ''}\n${url}&${parseUtmParams('email', utmMedium, utmContent, utmCampaign)}` }
    className="social-share-button email"
    data-ga-event={gaEvent}
    data-ga-event-category={gaEventCategory}
    data-ga-category={gaCategory}
    data-ga-action={gaAction}
    data-ga-label={gaLabel}
    { ...openInNewTab && { target: '_blank' } }
  >
    <SvgIcon {...{name: "envelope"}} />
    <span className="visually-hidden">{__( 'Share via', 'planet4-master-theme' )} Email</span>
  </a>
);
