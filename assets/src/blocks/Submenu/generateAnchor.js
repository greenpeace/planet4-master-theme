export const generateAnchor = (text, previousAnchors) => {
  const anchor = text.toLowerCase().trim().replace(/[^a-zA-Z\d:\u00C0-\u00FF]/g, '-');

  let i = 0,
    unique = anchor;

  while (previousAnchors.includes(unique)) {
    unique = `${anchor}-${++i}`;
  }

  return unique;
};
