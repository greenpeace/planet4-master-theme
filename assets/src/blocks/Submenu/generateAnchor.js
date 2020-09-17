export const generateAnchor = (text, previousHeadings) => {
  return text.toLowerCase().trim().replace(/ /g, '-');
};

