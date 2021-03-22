import { Columns } from './ColumnsEditor.js';
import { withSelect } from '@wordpress/data';

const { __ } = wp.i18n;

export class ColumnsBlock {
  constructor() {
    const { registerBlockType } = wp.blocks;

    registerBlockType('planet4-blocks/columns', {
      title: __('Columns', 'planet4-blocks-backend'),
      icon: 'grid-view',
      category: 'planet4-blocks',
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
          type: 'array',
          default: [],
          title: {
            type: 'string'
          },
          description: {
            type: 'string'
          },
          attachment: {
            type: 'integer'
          },
          cta_link: {
            type: 'string'
          },
          link_new_tab: {
            type: 'boolean'
          },
          cta_text: {
            type: 'string'
          },
        }
      },
      edit: withSelect((select, props) => {

        const { attributes } = props;
        const { columns } = attributes;
        let column_img = [];

        if (columns && 0 < columns.length) {

          for (let column of columns) {
            if (column['attachment'] && (0 < column['attachment'])) {

              let media_details = select('core').getMedia(column['attachment']);
              if (media_details) {
                column_img[column['attachment']] = select('core').getMedia(column['attachment']).source_url;
              }
            }
          }
        }

        return { column_img };

      })(({ isSelected, attributes, setAttributes, column_img }) => {

        function onTitleChange(value) {
          setAttributes({ columns_title: value });
        }

        function onDescriptionChange(value) {
          setAttributes({ columns_description: value });
        }

        function onSelectImage(index, value) {
          let { id } = value;
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].attachment = id;
          setAttributes({ columns });
        }

        function onSelectedLayoutChange(value) {
          setAttributes({ columns_block_style: value });

          if ('no_image' === value) {
            const { columns } = attributes;
            if (0 < columns.length) {
              let new_columns = [...columns];
              let i;
              for (i = 0; i < columns.length; i++) {
                new_columns[i]['attachment'] = 0;
              }
              setAttributes({ columns: new_columns });
            }
          }
        }

        function addColumn() {
          const { columns } = attributes;

          if (columns.length < 4) {
            setAttributes({
              columns: [...columns, {
                title: '',
                description: '',
                attachment: 0,
                cta_link: '',
                cta_text: '',
                link_new_tab: '',
              }]
            });
          }
        }

        function removeColumn() {
          setAttributes({ columns: attributes.columns.slice(0, -1) });
        }

        function onColumnHeaderChange(index, value) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].title = value;
          setAttributes({ columns });
        }

        function onColumnDescriptionChange(index, value) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].description = value;
          setAttributes({ columns });
        }

        function onCTALinkChange(index, value) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].cta_link = value;
          setAttributes({ columns });
        }

        function onLinkNewTabChange(index, value) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].link_new_tab = value;
          setAttributes({ columns });
        }

        function onCTAButtonTextChange(index, value) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].cta_text = value;
          setAttributes({ columns });
        }

        function onDeleteImage(index) {
          let columns = JSON.parse(JSON.stringify(attributes.columns));
          columns[index].attachment = 0;
          setAttributes({ columns });
        }

        return <Columns
          {...attributes}
          isSelected={isSelected}
          onSelectedLayoutChange={onSelectedLayoutChange}
          onTitleChange={onTitleChange}
          onDescriptionChange={onDescriptionChange}
          onSelectImage={onSelectImage}
          addColumn={addColumn}
          removeColumn={removeColumn}
          onColumnHeaderChange={onColumnHeaderChange}
          onColumnDescriptionChange={onColumnDescriptionChange}
          onCTALinkChange={onCTALinkChange}
          onLinkNewTabChange={onLinkNewTabChange}
          onCTAButtonTextChange={onCTAButtonTextChange}
          column_img={column_img}
          onDeleteImage={onDeleteImage}
        />
      }),
      save() {
        return null;
      }
    });
  };
}
