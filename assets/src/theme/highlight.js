const HIGHLIGHT_CLASS = 'theme-editor-highlight';
export const addHighlight = element => element.classList.add(HIGHLIGHT_CLASS);
export const removeHighlight = element => element.classList.remove(HIGHLIGHT_CLASS);

export const whileHoverHighlight = element => ({
  onMouseEnter: () => addHighlight(element),
  onMouseLeave: () => removeHighlight(element),
});
