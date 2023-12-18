export const getCaptionWithCredits = image => {
  const caption = image.caption || '';
  const credits = getCredits(image, caption);
  return `${caption}  ${credits}`.trim();
};

const getCredits = (image, caption) => {
  if (!image.credits || caption.includes(image.credits)) {
    return '';
  }

  return `© ${image.credits.replace(/^©\s*/, '')}`;
};
