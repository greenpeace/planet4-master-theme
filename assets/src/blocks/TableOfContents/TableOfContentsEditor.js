import {TableOfContentsLevel} from './TableOfContentsLevel';
import {TableOfContentsItems} from './TableOfContentsItems';
import {getTableOfContentsStyle} from './getTableOfContentsStyle';
import {makeHierarchical} from './makeHierarchical';
import {getHeadingsFromBlocks} from './getHeadingsFromBlocks';
import {deepClone} from '../../functions/deepClone';

const {useSelect, select, dispatch} = wp.data;
const {InspectorControls, RichText, BlockControls} = wp.blockEditor;
const {Button, PanelBody, ToolbarItem} = wp.components;
const {createBlock} = wp.blocks;
const {__} = wp.i18n;

/**
 * Renders the edit view of the Table of Contents block with controls for managing levels.
 *
 * @param {Object} attributes - The block attributes.
 * @param {Function} setAttributes - Function to update block attributes.
 * @return {JSX.Element} The rendered edit view.
 */
const renderEdit = (attributes, setAttributes) => {
  /**
   * Adds a new level to the Table of Contents.
   */
  function addLevel() {
    const [previousLastLevel] = attributes.levels.slice(-1);
    const newLevel = previousLastLevel.heading + 1;
    setAttributes({levels: attributes.levels.concat({heading: newLevel, link: false, style: 'none'})});
  }

  /**
   * Updates the heading level for a specific item.
   *
   * @param {number} index - Index of the level to update.
   * @param {string} value - New heading value.
   */
  function onHeadingChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].heading = Number(value);
    setAttributes({levels});
  }

  /**
   * Updates the link attribute for a specific item.
   *
   * @param {number} index - Index of the level to update.
   * @param {string} value - New link value.
   */
  function onLinkChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].link = value;
    setAttributes({levels});
  }

  /**
   * Updates the style attribute for a specific item.
   *
   * @param {number} index - Index of the level to update.
   * @param {string} value - New style value, can be "none", "bullet", or "number".
   */
  function onStyleChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].style = value;
    setAttributes({levels});
  }

  /**
   * Removes the last level from the Table of Contents.
   */
  function removeLevel() {
    setAttributes({levels: attributes.levels.slice(0, -1)});
  }

  /**
   * Gets the minimum heading level for a specific index.
   *
   * @param {Object} attr - Block attributes.
   * @param {number} index - Index of the level.
   * @return {number|null} Minimum heading value or null for the first index.
   */
  function getMinLevel(attr, index) {
    if (index === 0) {
      return null;
    }
    return attr.levels[index - 1].heading;
  }

  return (
    <InspectorControls>
      <PanelBody title={__('Settings', 'planet4-blocks-backend')}>
        <p className="components-base-control__help">
          {__('Choose the headings to be displayed in the table of contents.', 'planet4-blocks-backend')}
        </p>
        {attributes.levels.map((level, i) => (
          <TableOfContentsLevel
            {...level}
            onHeadingChange={onHeadingChange}
            onLinkChange={onLinkChange}
            onStyleChange={onStyleChange}
            index={i}
            key={i}
            minLevel={getMinLevel(attributes, i)}
          />
        ))}
        <Button
          isPrimary
          onClick={addLevel}
          disabled={attributes.levels.length >= 3 || attributes.levels.slice(-1)[0].heading === 0}
          style={{marginRight: 5}}
        >
          {__('Add level', 'planet4-blocks-backend')}
        </Button>
        <Button
          variant="secondary"
          onClick={removeLevel}
          disabled={attributes.levels.length <= 1}
        >
          {__('Remove level', 'planet4-blocks-backend')}
        </Button>
      </PanelBody>
      <PanelBody title={__('Learn more about this block', 'planet4-blocks-backend')} initialOpen={false}>
        <p className="components-base-control__help">
          <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/table-of-contents/" rel="noreferrer">
            P4 Handbook P4 Table of Contents
          </a>
          {' '} &#128203;
        </p>
      </PanelBody>
    </InspectorControls>
  );
};

/**
 * Renders the view of the Table of Contents block.
 *
 * @param {Object} attributes - The block attributes.
 * @param {Function} setAttributes - Function to update block attributes.
 * @param {string} className - The CSS class for the block.
 * @return {JSX.Element} The rendered view.
 */
const renderView = (attributes, setAttributes, className) => {
  const {
    title,
    levels,
    submenu_style,
    isExample,
    exampleMenuItems,
  } = attributes;

  const blocks = useSelect(wpSelect => wpSelect('core/block-editor').getBlocks(), null);
  const flatHeadings = getHeadingsFromBlocks(blocks, levels);
  const menuItems = isExample ? exampleMenuItems : makeHierarchical(flatHeadings);
  const style = getTableOfContentsStyle(className, submenu_style);

  return (
    <>
      <BlockControls>
        <ToolbarItem
          as={Button}
          onClick={() => convertIntoListBlock(menuItems)}
        >
          Convert to static list
        </ToolbarItem>
      </BlockControls>
      <section className={`block table-of-contents-block table-of-contents-${style} ${className ?? ''}`}>
        <RichText
          tagName="h2"
          placeholder={__('Enter title', 'planet4-blocks-backend')}
          value={title}
          onChange={titl => setAttributes({title: titl})}
          withoutInteractiveFormatting
          allowedFormats={[]}
        />
        {menuItems.length > 0 ? (
          <TableOfContentsItems menuItems={menuItems} />
        ) : (
          <div className="EmptyMessage">
            {__('There are not any pre-established headings that this block can display in the form of a table of content. Please add headings to your page or choose another heading size.', 'planet4-blocks-backend')}
          </div>
        )}
      </section>
    </>
  );
};

/**
 * Creates a list block with list item blocks based on the given items.
 *
 * @param {Array} items - The items to create list blocks from. Each item should have `text`, `shouldLink`, and `children`.
 * @return {Object} The core/list block with the nested structure.
 */
const createListBlocks = items => {
  const innerBlocks = [];

  items.forEach(item => {
    let content = item.text;

    if (item.shouldLink) {
      content = `<a href="#${item.anchor}">${content}</a>`;
    }

    const newInnerBlock = createBlock('core/list-item', {className: `list-style list-style-${item.style}`, content});

    if (item.children && item.children.length > 0) {
      const childListBlock = createListBlocks(item.children);
      newInnerBlock.innerBlocks = [childListBlock];
    }

    innerBlocks.push(newInnerBlock);
  });

  return createBlock('core/list', {}, innerBlocks);
};

/**
 * Converts the given menu items into a static list block and replaces the current block.
 *
 * @param {Array} menuItems - The menu items to convert into a list block.
 */
const convertIntoListBlock = menuItems => {
  if (!menuItems) {
    return;
  }

  const blockList = select('core/block-editor').getBlocks();
  const blockIndex = blockList.findIndex(block => block.name === 'planet4-blocks/submenu');

  if (blockIndex === -1) {
    return;
  }

  let blockClassName = blockList[blockIndex].attributes.className;
  blockClassName = blockClassName.replace(/^is-style-/, '');

  const headingBlock = createBlock('core/heading', {content: blockList[blockIndex].attributes.title});
  const listBlocks = createListBlocks(menuItems);

  const groupBlock = createBlock('core/group', {className: `table-of-contents is-style-${blockClassName}`}, [headingBlock, listBlocks]);

  dispatch('core/block-editor').insertBlock(groupBlock, blockIndex);
  // dispatch('core/block-editor').removeBlock(blockList[blockIndex].clientId);
};

/**
 * Renders the Table of Contents block editor.
 *
 * @param {Object} props - The component props.
 * @param {Object} props.attributes - The block attributes.
 * @param {Function} props.setAttributes - Function to update block attributes.
 * @param {boolean} props.isSelected - Indicates if the block is selected.
 * @param {string} props.className - The CSS class for the block.
 * @return {JSX.Element} The Table of Contents editor component.
 */
export const TableOfContentsEditor = ({attributes, setAttributes, isSelected, className}) => (
  <>
    {isSelected && renderEdit(attributes, setAttributes)}
    {renderView(attributes, setAttributes, className)}
  </>
);
