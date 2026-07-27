import {generateAnchor} from './generateAnchor';

const getHeadingLevel = heading => Number(heading.tagName.replace('H', ''));

export const getHeadingsFromDom = selectedLevels => {
  const container = document.querySelector('.page-content');

  if (!container || !selectedLevels) {
    return [];
  }

  // Get all heading tags that we need to query
  const headingsSelector = selectedLevels.map(({heading}) => `h${heading}`).join(',');

  const usedAnchors = [];

  return [...container.querySelectorAll(headingsSelector)]
    .filter(heading => (
      !heading.closest('.table-of-contents-block') &&
      !heading.closest('.secondary-navigation-block')
    ))
    .map(heading => {
      const levelConfig = selectedLevels.find(
        selected => selected.heading === getHeadingLevel(heading)
      );

      if (!levelConfig) {
        return null;
      }

      if (!heading.id) {
        heading.id = generateAnchor(
          heading.textContent,
          usedAnchors
        );
      }

      usedAnchors.push(heading.id);

      return {
        content: heading.textContent,
        level: levelConfig.heading,
        style: levelConfig.style,
        shouldLink: levelConfig.link,
        anchor: heading.id,
      };
    })
    .filter(Boolean);
};
