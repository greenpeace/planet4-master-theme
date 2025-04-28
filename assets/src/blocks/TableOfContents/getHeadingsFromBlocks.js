import {generateAnchor} from './generateAnchor';
import {unescape} from '../../functions/unescape';

// We can put the other blocks that can have a heading inside in here along with the attribute containing the heading text.
// Then we can also filter those to include them in the menu.
const blockTypesWithHeadings = [
  {name: 'core/query', fieldName: 'core/heading', level: 4},
];

// Naive regex to remove html tags. Don't use anywhere else as it's too limited, but for the expected HTML in heading
// blocks this should be sufficient.
const stripTags = str => str.replace(/(<([^>]+)>)/ig, ''); //NOSONAR

export const getHeadingsFromBlocks = (blocks, selectedLevels) => {
  const headings = [];
  blocks.forEach(block => {
    if (block.name === 'core/heading') {
      const blockLevel = block.attributes.level;

      const levelConfig = selectedLevels.find(selected => selected.heading === blockLevel);

      if (!levelConfig) {
        return;
      }

      const anchor = block.attributes.anchor || generateAnchor(block.attributes.content, headings.map(h => h.anchor));

      headings.push({
        level: blockLevel,
        // The content of RichText elements will always come out escaped. This is problematic as those will be displayed
        // literally when we render them. It seems safe to unescape here as the value will not be used without escaping.
        content: unescape(stripTags(block.attributes.content)),
        anchor,
        style: levelConfig.style,
        shouldLink: levelConfig.link,
      });

      return;
    }

    if (block.name === 'core/freeform') {
      const parser = new DOMParser();
      const selector = selectedLevels.map(({heading}) => `h${heading}`).join();
      const doc = parser.parseFromString(block.attributes.content, 'text/html');

      const classicHeadings = doc.querySelectorAll(selector);

      headings.push(...[...classicHeadings].map(h => {
        const blockLevel = parseInt(h.tagName.replace('H', ''));
        const levelConfig = selectedLevels.find(selected => selected.heading === blockLevel);

        const anchor = h.id || generateAnchor(h.innerText, headings.map(hh => hh.anchor));

        return ({
          level: blockLevel,
          content: h.innerText,
          anchor,
          style: levelConfig.style,
          shouldLink: levelConfig.link,
        });
      }));

      return;
    }

    const blockType = blockTypesWithHeadings.find(({name}) => name === block.name);

    if (blockType) {
      const {fieldName, level} = blockType;
      const levelConfig = selectedLevels.find(selected => selected.heading === level);

      if (!levelConfig) {
        return;
      }
      headings.push({
        level,
        content: block.attributes[fieldName],
      });
    }
  });

  return headings;
};

