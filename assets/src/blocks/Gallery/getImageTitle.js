export const getImageTitle = (image) => {
  const caption = image.caption || '';
  const credits = image.credits && !caption.includes(image.credits)
    ? (image.credits.includes('©') ? image.credits : `© ${image.credits}`) : '';
  return `${caption}  ${credits}`.trim();
};
