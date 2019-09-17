import {Submenu} from './Submenu.js';

export class SubmenuBlock {
  constructor() {
    const {registerBlockType} = wp.blocks;
    const {withSelect} = wp.data;

    registerBlockType('planet4-blocks/submenu', {
      title: 'Submenu',
      icon: 'welcome-widgets-menus',
      category: 'planet4-blocks',
      supports: {
        multiple: false, // Use the block just once per post.
      },
      /**
       * Transforms old 'shortcake' shortcode to new gutenberg block.
       *
       * old block-shortcode:
       * [shortcake_submenu submenu_style="3" title="title22" heading1="2"
       *                    link1="true" style1="bullet" heading2="3" link2="true" style2="number"
       *                    heading3="4" link3="false"
       * /]
       *
       * new block-gutenberg:
       * <!-- wp:planet4-blocks/submenu {"submenu_style":3,"title":"title22","levels":[{"heading":"2","link":"true","style":"bullet"},
       *    {"heading":"3","link":"true","style":"number"},{"heading":"4","link":"false","style":"none"}]} /-->
       *
       */
      transforms: {
        from: [
          {
            type: 'shortcode',
            // Shortcode tag can also be an array of shortcode aliases
            // This `shortcode` definition will be used as a callback,
            // it is a function which expects an object with at least
            // a `named` key with `cover_type` property whose default value is 1.
            // See: https://simonsmith.io/destructuring-objects-as-function-parameters-in-es6
            tag: 'shortcake_submenu',
            attributes: {
              submenu_style: {
                type: 'integer',
                shortcode: function (attributes) {
                  return Number(attributes.named.submenu_style);
                }
              },
              title: {
                type: 'string',
                shortcode: function (attributes) {
                  return attributes.named.title;
                }
              },
              levels: {
                type: 'array',
                shortcode: function (attributes) {
                  let levels = [];
                  if (attributes.named.heading1 > 0) {
                    let level = {
                      heading: Number(attributes.named.heading1),
                      link: Boolean(attributes.named.link1) || false,
                      style: attributes.named.style1 || 'none'
                    };
                    levels.push(Object.assign({}, level));

                    if (attributes.named.heading2 > 0) {
                      let level = {
                        heading: Number(attributes.named.heading2),
                        link: Boolean(attributes.named.link2) || false,
                        style: attributes.named.style2 || 'none'
                      };
                      levels.push(Object.assign({}, level));

                      if (attributes.named.heading3 > 0) {
                        let level = {
                          heading: Number(attributes.named.heading3),
                          link: Boolean(attributes.named.link3) || false,
                          style: attributes.named.style3 || 'none'
                        };
                        levels.push(Object.assign({}, level));
                      }
                    }
                  }
                  return levels;
                },
              }
            },
          },
        ]
      },
      attributes: {
        submenu_style: {
          type: 'integer',
          default: 1
        },
        title: {
          type: 'string',
        },
        levels: {
          type: 'array',
          default: [ {heading: 0, link: false, style: 'none'}]
        },
      },
      edit: withSelect((select) => {

      })(({
            isSelected,
            attributes,
            setAttributes
          }) => {

        function addLevel() {
          setAttributes({levels: attributes.levels.concat({heading: 0, link: false, style: 'none'})});
        }

        function onTitleChange(value) {
          setAttributes({title: value});
        }

        function onHeadingChange(index, value) {
          let levels = JSON.parse(JSON.stringify(attributes.levels));
          levels[index].heading = Number(value);
          setAttributes({levels: levels});
        }

        function onLayoutChange(value) {
          setAttributes({submenu_style: Number(value)});
        }

        function onLinkChange(index, value) {
          let levels = JSON.parse(JSON.stringify(attributes.levels));
          levels[index].link = value;
          setAttributes({levels: levels});
        }

        function onStyleChange(index, value) {
          let levels = JSON.parse(JSON.stringify(attributes.levels));
          levels[index].style = value;
          setAttributes({levels: levels});
        }

        function removeLevel() {
          setAttributes({levels: attributes.levels.slice(0, -1)});
        }

        return <Submenu
          {...attributes}
          isSelected={isSelected}
          onSelectedLayoutChange={onLayoutChange}
          onTitleChange={onTitleChange}
          onHeadingChange={onHeadingChange}
          onLinkChange={onLinkChange}
          onStyleChange={onStyleChange}
          addLevel={addLevel}
          removeLevel={removeLevel}
        />
      }),
      save() {
        return null;
      }
    });
  };
}

