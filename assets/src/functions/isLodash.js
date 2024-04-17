// Determines if _ is lodash or not.
export const isLodash = () => {
  let isItLodash = false;

  // If _ is defined and the function _.forEach exists then we know underscore OR lodash are in place
  // eslint-disable-next-line no-undef
  if ('undefined' !== typeof (_) && 'function' === typeof (_.forEach)) {
    // A small sample of some of the functions that exist in lodash but not underscore
    const funcs = ['get', 'set', 'at', 'cloneDeep'];

    // Simplest if assume exists to start
    isItLodash = true;

    funcs.forEach(func => {
      // If just one of the functions do not exist, then not lodash
      // eslint-disable-next-line no-undef
      isItLodash = ('function' !== typeof (_[func])) ? false : isItLodash;
    });
  }

  if (isItLodash) {
    // We know that lodash is loaded in the _ variable
    return true;
  }
  // We know that lodash is NOT loaded
  return false;
};
