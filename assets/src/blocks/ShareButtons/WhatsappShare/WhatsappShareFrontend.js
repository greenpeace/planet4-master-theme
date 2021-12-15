import { SvgIcon } from "../SvgIcon";
import { parseUtmParams } from '../utils';

const { __ } = wp.i18n;

export const WhatsappShareFrontend = ({
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
  <a href={ `${baseSharedUrl}?text=${encodeURIComponent(url)}&${parseUtmParams('whatsapp', utmMedium, utmContent, utmCampaign)}` }
    className="social-share-button whatsapp"
    data-ga-event={gaEvent}
    data-ga-event-category={gaEventCategory}
    data-ga-category={gaCategory}
    data-ga-action={gaAction}
    data-ga-label={gaLabel}
    { ...openInNewTab && { target: '_blank' } }
  >
    {/* <SvgIcon {...{name: "whatsapp"}} /> */}
    <svg viewBox="0 0 32 32">
      <use xlinkHref={ `${window.p4bk_vars.themeUrl}#whatsapp` } />
    </svg>
    <span className="visually-hidden">{__( 'Share on', 'planet4-master-theme' )} Whatsapp</span>
  </a>
);
