import {renderToString} from 'react-dom/server';
import {ArticlesEditor} from './ArticlesEditor';
import {frontendRendered} from '../../functions/frontendRendered';
import {ArticlesFrontend} from './ArticlesFrontend';

const {__} = wp.i18n;

const BLOCK_NAME = 'planet4-blocks/articles';

const attributes = {
  article_heading: {
    type: 'string',
    default: __('Related Articles', 'planet4-blocks'),
  },
  articles_description: {
    type: 'string',
    default: '',
  },
  article_count: {
    type: 'integer',
    default: 3,
  },
  tags: {
    type: 'array',
    default: [],
  },
  posts: {
    type: 'array',
    default: [],
  },
  post_types: {
    type: 'array',
    default: [],
  },
  read_more_text: {
    type: 'string',
    default: __('Load more', 'planet4-blocks'),
  },
  read_more_link: {
    type: 'string',
    default: '',
  },
  button_link_new_tab: {
    type: 'boolean',
    default: false,
  },
  ignore_categories: {
    type: 'boolean',
    default: false,
  },
};

export const registerArticlesBlock = () => {
  const {registerBlockType} = wp.blocks;
  const {RawHTML} = wp.element;

  registerBlockType(BLOCK_NAME, {
    title: 'Articles',
    icon: 'excerpt-view',
    category: 'planet4-blocks',
    supports: {
      html: false, // Disable "Edit as HTMl" block option.
    },
    attributes,
    edit: ArticlesEditor,
    save: props => {
      const markup = renderToString(
        <div
          data-hydrate={BLOCK_NAME}
          data-attributes={JSON.stringify(props.attributes)}
        >
          <ArticlesFrontend {...props} />
        </div>
      );
      return <RawHTML>{markup}</RawHTML>;
    },
    deprecated: [
      {
        attributes,
        save: frontendRendered(BLOCK_NAME),
      },
      {
        attributes,
        save() {
          return null;
        },
      },
    ],
  });
};
