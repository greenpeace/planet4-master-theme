export const getSizesUnder = (sizes, maxWidth) => sizes.filter(size => {
  return maxWidth ? size.width < maxWidth : true;
});

export const toSrcSet = (sizes, config) => {
  const sizesToUse = config && config.maxWidth ? getSizesUnder(sizes, config.maxWidth) : sizes;
  return sizesToUse.map(size => `${size.url} ${size.width}w`).join();
};
