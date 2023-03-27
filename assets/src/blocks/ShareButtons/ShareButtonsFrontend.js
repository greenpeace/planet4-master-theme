import {ShareButton} from './ShareButton';

const parseUrl = attrs => {
  switch (attrs.type) {
  case 'whatsapp':
    return `https://wa.me?text=${encodeURIComponent(attrs.url)}`;
  case 'facebook':
    return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(attrs.url)}`;
  case 'twitter':
    return `https://twitter.com/share
        ?url=${encodeURIComponent(attrs.url)}
        &text=${encodeURIComponent(attrs.text)}
        ${(attrs.description ? ' - ' + encodeURIComponent(attrs.description) : '')}
        ${(attrs.account ?
    ' via @' + encodeURIComponent(attrs.account) + '&related=' + encodeURIComponent(attrs.account) :
    '')}`;
  case 'email':
    return `mailto:?subject=${attrs.title}&body=${attrs.body ? encodeURIComponent(attrs.body) : ''}`;
  }
};

const parseUtmParams = ({utmSource, utmMedium, utmContent, utmCampaign}) => (
  [
    utmSource ? `utm_source=${encodeURIComponent(utmSource)}` : null,
    utmMedium ? `utm_medium=${encodeURIComponent(utmMedium)}` : null,
    utmContent ? `utm_content=${encodeURIComponent(utmContent)}` : null,
    utmCampaign ? `utm_campaign=${encodeURIComponent(utmCampaign)}` : null,
  ].filter(x => x).join('&')
);

export const ShareButtonsFrontend = ({
  url,
  openInNewTab,
  utmMedium,
  utmContent,
  utmCampaign,
  gaCategory,
  gaAction,
  gaLabel,
  buttons,
}) => (
  <nav className="share-buttons">
    {buttons.map(button => button.showInMenu ? <ShareButton key={button.type} {...{
      href: `
        ${parseUrl({...button, url})}
        &${parseUtmParams({utmSource: button.type, utmMedium, utmContent, utmCampaign})}`,
      providerName: button.type,
      iconName: button.iconName,
      openInNewTab,
      gaCategory,
      gaAction,
      gaLabel,
      hiddenText: button.hiddenText,
    }} /> : null)}
  </nav>
);
