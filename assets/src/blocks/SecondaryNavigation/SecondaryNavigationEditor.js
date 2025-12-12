import {getHeadingsFromBlocks} from '../../functions/getHeadingsFromBlocks';

const {useSelect} = wp.data;
const {InspectorControls} = wp.blockEditor;
const {PanelBody} = wp.components;
const {__} = wp.i18n;

const renderEdit = () =>  (
  <InspectorControls>
    <PanelBody title={__('Learn more about this block', 'planet4-master-theme-backend')} initialOpen={false}>
      <p className="components-base-control__help">
        <a target="_blank" href="https://planet4.greenpeace.org/content/blocks/secondary-navigation/" rel="noreferrer">
            P4 Handbook - P4 Secondary Navigation Menu
        </a>
        {' '} &#128203;
      </p>
    </PanelBody>
  </InspectorControls>
);

const renderView = ({levels}) => {
  const blocks = useSelect(select => select('core/block-editor').getBlocks(), []);
  const menuItems = getHeadingsFromBlocks(blocks, levels);

  return (
    <section className="block secondary-navigation-block">
      {menuItems.length > 0 ?
        <div className="secondary-navigation-menu">
          <ul className="secondary-navigation-items justify-content-center">
            {menuItems.map(({anchor, content}) => (
              <li key={anchor}>
                <a
                  className="secondary-navigation-link"
                  href={`#${anchor}`}
                >
                  {content}
                </a>
              </li>
            ))}
          </ul>
        </div> :
        <div className="EmptyMessage">
          {__('There are not any pre-established headings that this block can display in the form of a secondary navigation menu. Please add headings to your page.', 'planet4-master-theme-backend')}
        </div>
      }
    </section>
  );
};

// eslint-disable-next-line no-unused-vars
export const SecondaryNavigationEditor = ({attributes, isSelected}) => (
  <>
    {isSelected && renderEdit()}
    {renderView(attributes)}
  </>
);
