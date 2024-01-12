export const getStyleLabel = (label, help) => {
  if (help) {
    return `${label} - ${help}`;
  }
  return label;
};
