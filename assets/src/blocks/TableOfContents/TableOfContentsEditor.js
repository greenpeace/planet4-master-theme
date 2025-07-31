import {TableOfContentsLevel} from './TableOfContentsLevel';
import {TableOfContentsItems} from './TableOfContentsItems';
import {getTableOfContentsStyle} from './getTableOfContentsStyle';
import {makeHierarchical} from './makeHierarchical';
import {getHeadingsFromBlocks} from '../../functions/getHeadingsFromBlocks';
import {deepClone} from '../../functions/deepClone';

const {useSelect} = wp.data;
const {InspectorControls, RichText} = wp.blockEditor;
const {Button, PanelBody} = wp.components;
const {__} = wp.i18n;

const renderEdit = (attributes, setAttributes) => {
  function addLevel() {
    const [previousLastLevel] = attributes.levels.slice(-1);
    const newLevel = previousLastLevel.heading + 1;
    setAttributes({levels: attributes.levels.concat({heading: newLevel, link: true, style: 'none'})});
  }

  function onHeadingChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].heading = Number(value);
    setAttributes({levels});
  }

  function onLinkChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].link = value;
    setAttributes({levels});
  }

  function onStyleChange(index, value) {
    const levels = deepClone(attributes.levels);
    levels[index].style = value; // Possible values: "none", "bullet", "number"
    setAttributes({levels});
  }

  function removeLevel() {
    setAttributes({levels: attributes.levels.slice(0, -1)});
  }

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

const renderView = (attributes, setAttributes, className) => {
  const {
    title,
    levels,
    submenu_style,
    isExample,
    exampleMenuItems,
  } = attributes;

  const blocks = useSelect(select => select('core/block-editor').getBlocks(), null);

  const flatHeadings = getHeadingsFromBlocks(blocks, levels);

  const menuItems = isExample ? exampleMenuItems : makeHierarchical(flatHeadings);

  const style = getTableOfContentsStyle(className, submenu_style);

  return (
    <section className={`block table-of-contents-block table-of-contents-${style} ${className ?? ''}`}>
      <RichText
        tagName="h2"
        placeholder={__('Enter title', 'planet4-blocks-backend')}
        value={title}
        onChange={titl => setAttributes({title: titl})}
        withoutInteractiveFormatting
        allowedFormats={[]}
      />
      {menuItems.length > 0 ?
        <TableOfContentsItems menuItems={menuItems} /> :
        <div className="EmptyMessage">
          {__('There are not any pre-established headings that this block can display in the form of a table of content. Please add headings to your page or choose another heading size.', 'planet4-blocks-backend')}
        </div>
      }
    </section>
  );
};

export const TableOfContentsEditor = ({attributes, setAttributes, isSelected, className}) => (
  <>
    {isSelected && renderEdit(attributes, setAttributes)}
    {renderView(attributes, setAttributes, className)}
  </>
);
