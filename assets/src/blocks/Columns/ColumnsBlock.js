import {Columns} from './Columns.js';

const {__} = wp.i18n;

export class ColumnsBlock {
    constructor() {
      const {registerBlockType} = wp.blocks;

      registerBlockType('planet4-blocks/columns', {
        title: __('Columns', 'p4ge'),
        icon: "grid-view",
        category: 'planet4-blocks',
        /**
         * Transforms old 'shortcake' shortcode to new gutenberg block.
         *
         * old block-shortcode:
         * [shortcake_columns columns_block_style="image" columns_title="Lorem Ipsum" columns_description="Lorem Ipsum"
         *                    title_1="col1" description_1="col1 body" attachment_1="5096" link_1="cta link1" link_new_tab_1="true" cta_text_1="cta text"
         *                    title_2="col2" description_2="col2 body" attachment_2="5186" link_2="cta link2" link_new_tab_2="true" cta_text_2="cta text 2" link_new_tab_3="false" link_new_tab_4="false" /]
         * /]
         *
         * new block-gutenberg:
         * <!-- wp:planet4-blocks/columns {"columns_block_style":"image","columns_title":"Lorem Ipsum","columns_description":"Lorem Ipsum",
         *      "columns":[{"title":"col1","description":"col1 body","attachment":5096,"cta_link":"cta link1","cta_text":"cta text 1","link_new_tab":true},{"title":"col2","description":"col2 body","attachment":5186,"cta_link":"cta link2","cta_text":"cta text 2","link_new_tab":true}]} /-->
         *
         */
        transforms: {
          from: [
            {
              type: 'shortcode',
              // Shortcode tag can also be an array of shortcode aliases
              tag: 'shortcake_columns',
              attributes: {
                columns_block_style: {
                  type: 'string',
                  shortcode: ({named: {columns_block_style = ''}}) => columns_block_style,
                },
                columns_title: {
                  type: 'string',
                  shortcode: ({named: {columns_title = ''}}) => columns_title,
                },
                columns_description: {
                  type: 'string',
                  shortcode: ({named: {columns_description = ''}}) => columns_description,
                },
                columns: {
                  type: 'array',
                  shortcode: function (attributes) {
                    let columns = [];
                    if (attributes.named.title_1) {
                      let column = {
                        title: attributes.named.title_1,
                        description: attributes.named.description_1 || ''
                      };
                      if (attributes.named.columns_block_style != 'no_image') {
                        column.attachment = attributes.named.attachment_1 || false;
                      }
                      column.cta_link = attributes.named.link_1 || '';
                      column.link_new_tab = attributes.named.link_new_tab_1 || false;
                      column.cta_text = attributes.named.cta_text_1 || '';
                      columns.push(Object.assign({}, column));

                      if (attributes.named.title_2) {
                        let column = {
                          title: attributes.named.title_2,
                          description: attributes.named.description_2 || ''
                        };
                        if (attributes.named.columns_block_style != 'no_image') {
                          column.attachment = attributes.named.attachment_2 || false;
                        }
                        column.cta_link = attributes.named.link_2 || '';
                        column.link_new_tab = attributes.named.link_new_tab_2 || false;
                        column.cta_text = attributes.named.cta_text_2 || '';
                        columns.push(Object.assign({}, column));


                        if (attributes.named.title_3) {
                          let column = {
                            title: attributes.named.title_3,
                            description: attributes.named.description_3 || ''
                          };
                          if (attributes.named.columns_block_style != 'no_image') {
                            column.attachment = attributes.named.attachment_3 || false;
                          }
                          column.cta_link = attributes.named.link_3 || '';
                          column.link_new_tab = attributes.named.link_new_tab_3 || false;
                          column.cta_text = attributes.named.cta_text_3 || '';
                          columns.push(Object.assign({}, column));

                          if (attributes.named.title_4) {
                            let column = {
                              title: attributes.named.title_4,
                              description: attributes.named.description_4 || ''
                            };
                            if (attributes.named.columns_block_style != 'no_image') {
                              column.attachment = attributes.named.attachment_4 || false;
                            }
                            column.cta_link = attributes.named.link_4 || '';
                            column.link_new_tab = attributes.named.link_new_tab_4 || false;
                            column.cta_text = attributes.named.cta_text_4 || '';
                            columns.push(Object.assign({}, column));
                          }
                        }
                      }
                    }
                    return columns;
                  },
                }
              },
            },
          ]
        },
        attributes: {
          columns_block_style: {
            type: 'string',
          },
          columns_title: {
            type: 'string'
          },
          columns_description: {
            type: 'string'
          },
          columns: {
            type: "array",
            default: [],
            title:{
              type: 'string'
            },
            description:{
              type: 'string'
            },
            attachment:{
              type: 'integer'
            },
            cta_link:{
              type: 'string'
            },
            link_new_tab:{
              type: 'string'
            },
            cta_text:{
              type: 'string'
            },
          }
        },
        edit: ({isSelected, attributes, setAttributes}) => {

          function onTitleChange(value) {
            setAttributes({columns_title: value});
          }

          function onDescriptionChange(value) {
            setAttributes({columns_description: value});
          }

          function onSelectImage(index, value) {
            const {columns} = attributes;
            let {id}          = value;
            let new_columns   = [...columns];
            new_columns[index]['attachment'] = id;
            setAttributes({columns: new_columns});
          }

          function onSelectURL(index, value) {
            const {columns} = attributes;
            let {id} = null;
            let new_columns = [...columns];
            new_columns[index]['attachment'] = id;
            setAttributes({columns: new_columns});
          }

          function onUploadError({message}) {
            console.log(message);
          }

          function onSelectedLayoutChange( value ) {
            setAttributes({columns_block_style: value});

            if ( 'no_image' == value ) {
              const {columns} = attributes;
              if ( 0 < columns.length ) {
                let new_columns = [...columns];
                let i;
                for ( i = 0; i < columns.length; i++ ) {
                  new_columns[i]['attachment'] = 0;
                }
                setAttributes({columns: new_columns});
              }
            }
          }

          function addColumn() {
            const {columns} = attributes;

            if ( columns.length < 4 ) {
              setAttributes({
                columns: [...columns, {
                  title:'',
                  description:'',
                  attachment:0,
                  cta_link:'',
                  cta_text:'',
                  link_new_tab:'',
                }]
              });
            }
          }

          function removeColumn() {
            setAttributes({columns: attributes.columns.slice(0, -1) });
          }

          function onColumnHeaderChange(index, value) {
            let columns = JSON.parse(JSON.stringify(attributes.columns));
            columns[index].title = value;
            setAttributes({columns});
          }

          function onColumnDescriptionChange(index, value) {
            let columns = JSON.parse(JSON.stringify(attributes.columns));
            columns[index].description = value;
            setAttributes({columns});
          }

          function onCTALinkChange(index, value) {
            let columns = JSON.parse(JSON.stringify(attributes.columns));
            columns[index].cta_link = value;
            setAttributes({columns});
          }

          function onLinkNewTabChange(index, value) {
            let columns = JSON.parse(JSON.stringify(attributes.columns));
            columns[index].link_new_tab = value;
            setAttributes({columns});
          }

          function onCTAButtonTextChange(index, value) {
            let columns = JSON.parse(JSON.stringify(attributes.columns));
            columns[index].cta_text = value;
            setAttributes({columns});
          }

          return <Columns
            {...attributes}
            isSelected={isSelected}
            onSelectedLayoutChange={onSelectedLayoutChange}
            onTitleChange={onTitleChange}
            onDescriptionChange={onDescriptionChange}
            onSelectImage={onSelectImage}
            onSelectURL={onSelectURL}
            addColumn={addColumn}
            removeColumn={removeColumn}
            onUploadError={onUploadError}
            onColumnHeaderChange={onColumnHeaderChange}
            onColumnDescriptionChange={onColumnDescriptionChange}
            onCTALinkChange={onCTALinkChange}
            onLinkNewTabChange={onLinkNewTabChange}
            onCTAButtonTextChange={onCTAButtonTextChange}
          />
        },
        save() {
          return null;
        }
      });
    };
}
