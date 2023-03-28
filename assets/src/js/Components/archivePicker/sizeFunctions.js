export const getSizesUnder = (sizes, maxWidth) => sizes.filter(size => {
  if (maxWidth) {
    return size.width < maxWidth;
  }
  return true;
});

export const toSrcSet = (sizes, config) => {
  const sizesToUse = config && config.maxWidth ? getSizesUnder(sizes, config.maxWidth) : sizes;

  return sizesToUse.map(size => `${size.url} ${size.width}w`).join();
};
