import {useScript, removeScript} from '../../components/useScript/useScript';

//  Find Hubspot parameters in the embed code
//  Load the Hubspot form if found

export const HubspotEmbed = ({params}) => {
  const {
    use_embed_code,
    embed_code,
    target,
  } = params;
  const hbsptScript = 'https://js.hsforms.net/forms/v2.js';
  const hbsptRegex = new RegExp(/hbspt\.forms\.create\((.*)\);/ims);
  const matches = embed_code ? embed_code.match(hbsptRegex) : null;

  if (!use_embed_code || !embed_code || !matches || !matches[1]) {
    removeScript(hbsptScript, [use_embed_code, embed_code]);
    return null;
  }

  const hbsptParams = {};
  const jsonParams = matches[1].replace(/[\n\r\s+]/g, '');
  const paramsMatches = jsonParams.matchAll(/(?<key>[a-zA-Z]*):"(?<value>[^"]*)"/g);
  [...paramsMatches].forEach(e => {
    hbsptParams[e.groups.key] = e.groups.value;
  });

  const loadForm = () => {
    // eslint-disable-next-line no-undef
    hbspt.forms.create({
      region: hbsptParams.region ?? '',
      portalId: hbsptParams.portalId ?? null,
      formId: hbsptParams.formId ?? null,
      locale: document.getElementsByTagName('html')[0].getAttribute('lang')?.substring(0, 2),
      target,
    });
  };

  useScript(hbsptScript, loadForm, [use_embed_code, embed_code]);

  return null;
};
