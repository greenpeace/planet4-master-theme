export const addLinkTag = ({
  href,
  media = 'all',
  onLoad,
  onError,
}) => {
  const link = document.createElement('link');
  link.rel = 'stylesheet';
  link.href = href;
  link.media = media;

  link.addEventListener('load', onLoad);
  link.addEventListener('error', event => {
    onError(event);
  });

  document.body.appendChild(link);

  return link;
};

