import {generateAnchor} from '../TableOfContents/generateAnchor';
import {unescape} from '../../functions/unescape';

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
        content: unescape(stripTags(block.attributes.content)),
        anchor,
        shouldLink: levelConfig.link,
      });
    }
  });
  
  return headings;
};