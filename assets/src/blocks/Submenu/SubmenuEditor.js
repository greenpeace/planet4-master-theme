import { Fragment } from '@wordpress/element';
import { Button, PanelBody } from '@wordpress/components';
import { SubmenuLevel } from './SubmenuLevel';
import { SubmenuItems } from './SubmenuItems';
import { InspectorControls } from '@wordpress/block-editor';
import { getSubmenuStyle } from './getSubmenuStyle';
import { makeHierarchical } from './makeHierarchical';
import { getHeadingsFromBlocks} from './getHeadingsFromBlocks';
import { useSelect } from '@wordpress/data';

const { __ } = wp.i18n;
const { RichText } = wp.blockEditor;
const renderEdit = (attributes, setAttributes) => {
  function addLevel() {
    setAttributes({ levels: attributes.levels.concat({ heading: 0, link: false, style: 'none' }) });
  }

  function onHeadingChange(index, value) {
    let levels = JSON.parse(JSON.stringify(attributes.levels));
    levels[index].heading = Number(value);
    setAttributes({ levels });
  }

  function onLinkChange(index, value) {
    let levels = JSON.parse(JSON.stringify(attributes.levels));
    levels[index].link = value;
    setAttributes({ levels });
  }

  function onStyleChange(index, value) {
    let levels = JSON.parse(JSON.stringify(attributes.levels));
    levels[index].style = value; // Possible values: "none", "bullet", "number"
    setAttributes({ levels });
  }

  function removeLevel() {
    setAttributes({ levels: attributes.levels.slice(0, -1) });
  }

  return (
    <InspectorControls>
      <PanelBody title={__('Setting', 'planet4-blocks-backend')}>
        {attributes.levels.map((level, i) => (
          <SubmenuLevel
            {...level}
            onHeadingChange={onHeadingChange}
            onLinkChange={onLinkChange}
            onStyleChange={onStyleChange}
            index={i}
            key={i}
          />
        ))}
        <Button
          isPrimary
          onClick={addLevel}
          disabled={attributes.levels.length >= 3 || attributes.levels.slice(-1)[0].heading === 0}
          style={{ marginRight: 5 }}
        >
          {__('Add level', 'planet4-blocks-backend')}
        </Button>
        <Button
          isSecondary
          onClick={removeLevel}
          disabled={attributes.levels.length <= 1}
        >
          {__('Remove level', 'planet4-blocks-backend')}
        </Button>
      </PanelBody>
    </InspectorControls>
  );
}

const renderView = (attributes, setAttributes, className) => {

  const { blocks } = useSelect(select => {
    return ({ blocks: select('core/editor').getBlocks() });
  }, null);

  const flatHeadings = getHeadingsFromBlocks(blocks, attributes.levels);

  const menuItems = makeHierarchical(flatHeadings);

  const style = getSubmenuStyle(className, attributes.submenu_style);

  return (
    <section className={`block submenu-block submenu-${style}`}>
      <RichText
        tagName="h2"
        placeholder={__('Enter title', 'planet4-blocks-backend')}
        value={attributes.title}
        onChange={title => setAttributes({ title })}
        keepPlaceholderOnFocus={true}
        withoutInteractiveFormatting
        characterLimit={60}
        multiline="false"
      />
      {menuItems.length > 0 ?
        <SubmenuItems menuItems={menuItems} /> :
        <div className='EmptyMessage'>
          {__('The submenu block produces no output on the editor.', 'planet4-blocks-backend')}
        </div>
      }
    </section>
  );
}

export const SubmenuEditor = ({ attributes, setAttributes, isSelected, className }) => (
  <Fragment>
    {isSelected && renderEdit(attributes, setAttributes)}
    {renderView(attributes, setAttributes, className)}
  </Fragment>
);
