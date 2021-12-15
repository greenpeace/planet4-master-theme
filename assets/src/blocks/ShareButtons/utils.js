
export const parseUtmParams = (utm_source, utm_medium, utm_content, utm_campaign) => {
  return [
    utm_source ? `utm_source=${encodeURIComponent(utm_source)}` : null,
    utm_medium ? `utm_medium=${encodeURIComponent(utm_medium)}` : null,
    utm_content ? `utm_content=${encodeURIComponent(utm_content)}` : null,
    utm_campaign ? `utm_campaign=${encodeURIComponent(utm_campaign)}` : null,
  ].filter(x => x).join('&');
};
