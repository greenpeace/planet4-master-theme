/**
 * Get all blocks and inner blocks from a page.
 *
 * @param {Array} blocks - an array of blocks.
 *
 * @return {Array} All blocks from the page.
 */
export const getAllBlocks = blocks => blocks.flatMap(block => [block, ...getAllBlocks(block.innerBlocks || [])]);
