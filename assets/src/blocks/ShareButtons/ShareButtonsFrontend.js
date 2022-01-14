import { ShareButton } from './ShareButton';

const { __ } = wp.i18n;

const parseUtmParams = (utm_source, utm_medium, utm_content, utm_campaign) => {
  return [
    utm_source ? `utm_source=${encodeURIComponent(utm_source)}` : null,
    utm_medium ? `utm_medium=${encodeURIComponent(utm_medium)}` : null,
    utm_content ? `utm_content=${encodeURIComponent(utm_content)}` : null,
    utm_campaign ? `utm_campaign=${encodeURIComponent(utm_campaign)}` : null,
  ].filter(x => x).join('&');
};

export const ShareButtonsFrontend = ({
  url,
  utmMedium,
  utmContent,
  utmCampaign,
  gaCategory,
  gaAction,
  gaLabel,
  whatsapp,
  facebook,
  twitter,
  email,
}) => (
  <nav className='share-buttons'>
    {whatsapp.showInMenu && <ShareButton {...{
      href: `${whatsapp.baseSharedUrl}?text=${encodeURIComponent(url)}&${parseUtmParams('whatsapp', utmMedium, utmContent, utmCampaign)}`,
      providerName:'whatsapp',
      icon: 'whatsapp',
      openInNewTab: whatsapp.openInNewTab,
      gaCategory,
      gaAction,
      gaLabel,
      hiddenText: __( 'Share on Whatsapp', 'planet4-master-theme' ),
    }}/>}

    {facebook.showInMenu && <ShareButton {...{
      href: `${facebook.baseSharedUrl}?u=${encodeURIComponent(url)}&${parseUtmParams('facebook', utmMedium, utmContent, utmCampaign)}`,
      providerName:'facebook',
      icon: 'facebook-f',
      openInNewTab: facebook.openInNewTab,
      gaCategory,
      gaAction,
      gaLabel,
      hiddenText: __( 'Share on Facebook', 'planet4-master-theme' )
    }}/>}

    {twitter.showInMenu && <ShareButton {...{
      href: `
        ${twitter.baseSharedUrl}
        ?url=${encodeURIComponent(url)}
        &text=${encodeURIComponent(twitter.text)}
        &${parseUtmParams('facebook', utmMedium, utmContent, utmCampaign)}
        ${(twitter.description ? ` - ${encodeURIComponent(twitter.description)}` : '')}
        ${(twitter.account ? ` via @${encodeURIComponent(twitter.account)}&related=${encodeURIComponent(twitter.account)}` : '')}
      `,
      providerName:'twitter',
      icon: 'twitter',
      openInNewTab: twitter.openInNewTab,
      gaCategory,
      gaAction,
      gaLabel,
      hiddenText: __( 'Share on Twitter', 'planet4-master-theme' )
    }}/>}

    {email.showInMenu && <ShareButton {...{
      href: `mailto:?subject=${email.title}&body=${email.body ? encodeURIComponent(email.body) : ''}\n${url}&${parseUtmParams('email', utmMedium, utmContent, utmCampaign)}`,
      providerName:'email',
      icon: 'envelope',
      openInNewTab: email.openInNewTab,
      gaCategory,
      gaAction,
      gaLabel,
      hiddenText: __( 'Share on Email', 'planet4-master-theme' )
    }}/>}
  </nav>
);
