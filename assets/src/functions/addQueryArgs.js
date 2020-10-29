export const addQueryArgs = (path, args) => {
  if (typeof path !== 'string' || typeof args !== 'object') {
    return path;
  }

  Object.keys(args).forEach(k => {
    const value = args[k];
    if (typeof value === 'undefined' || value === '') {
      delete args[k];
      return;
    }
    if (Array.isArray(value)) {
      value.forEach((v, i) => {
        args[`${ k }[${ i }]`] = v;
      });
      delete args[k];
    }
  });

  return `${ path }?${ new URLSearchParams(args) }`;
};
