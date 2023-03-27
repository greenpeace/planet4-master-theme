/**
 * Get the name of the block style used from the classname string
 * given by Gutenberg
 *
 * @example
 * getStyleFromClassName('is-style-foo') => 'foo'
 * getStyleFromClassName('bar is-style-foo baz') => 'foo'
 * getStyleFromClassName('bar baz') => null
 *
 * @param {string} className
 * @return {string} new class name
 */
export const getStyleFromClassName = className => {
  if (!className || className.trim().length <= 9) {
    return null;
  }

  const styleClass = className.split(' ').filter(c => c.startsWith('is-style-'))[0];
  if (!styleClass) {
    return null;
  }

  return styleClass.replace(/^is-style-/, '');
};
