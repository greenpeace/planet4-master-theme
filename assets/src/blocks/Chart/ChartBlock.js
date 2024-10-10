import {renderToString} from 'react-dom/server';
import Editor from './ChartEditor';
import {ChartFrontend} from './ChartFrontend';

const {__} = wp.i18n;

const metadata = {
  "$schema": "https://schemas.wp.org/trunk/block.json",
  "apiVersion": 3,
  "name": "planet4-blocks/chart",
  "version": "0.1.0",
  "title": "Planet 4 Chart",
  "category": "planet4-blocks",
  "description": __("P4 Chart", 'planet4-blocks-backend'),
  "example": {},
  "supports": {
      "html": false
  },
};

export const registerChartBlock = () => {
  const {title, category, name, supports, apiVersion} = metadata;
  const {RawHTML} = wp.element;
  console.log('registerBlockType %s with category', name, category);
  wp.blocks.registerBlockType( name, {
    attributes: {
      chartType: {
        type: 'string',
        enum: [ 'bar', 'line', 'area', 'pie', 'donut' ],
        default: '',
      },
      dataType: {
        type: 'string',
        enum: [ 'csv', 'json' ],
        default: '',
      },
      dataUrl: {
        type: 'string',
        default: '',
      },
      width: {
        type: 'number',
        default: 400,
      },
      height: {
        type: 'number',
        default: 400,
      },
      axis: {type: 'object', default: {
        x: {
          field: '',
          type: '',
        },
        y: {
          field: '',
          type: '',
        },
        z: {
          field: '',
          type: '',
        },
      }},
    },
    category,
    title,
    icon: 'dashboard',
    supports,
    apiVersion,
    edit: Editor,
    save: props => {
      console.log(props)
      const markup = renderToString(
        <div
          data-hydrate={name}
          data-attributes={JSON.stringify(props.attributes)}
        >
          <ChartFrontend {...props.attributes} />
        </div>
      );
      return <RawHTML>{markup}</RawHTML>;
    },
  });
}