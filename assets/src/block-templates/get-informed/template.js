import gravityFormWithText from '../templates/gravity-form-with-text';
import {getPostListBlockTemplate, POSTS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/PostsList';

const {__} = wp.i18n;

const template = () => ([
  ['core/group', {className: 'block'}, [
    ['planet4-block-templates/quick-links', {
      title: __('Explore by topics', 'planet4-master-theme'),
    }],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('Topic 1', 'planet4-master-theme'),
    }],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('Topic 2', 'planet4-master-theme'),
      mediaPosition: 'right',
    }],
    ['planet4-block-templates/side-image-with-text-and-cta', {
      title: __('Topic 3', 'planet4-master-theme'),
    }],
    ['planet4-block-templates/issues', {
      title: __('The issues we work on', 'planet4-master-theme'),
    }],
    ['core/query',
      POSTS_LIST_BLOCK_ATTRIBUTES,
      getPostListBlockTemplate(__('Our recent victories', 'planet4-master-theme')),
    ],
    ['planet4-blocks/gallery', {
      className: 'is-style-grid',
      gallery_block_title: __('Our latest actions around the world', 'planet4-master-theme'),
    }],
    ['core/query',
      POSTS_LIST_BLOCK_ATTRIBUTES,
      getPostListBlockTemplate(__('Latest news & stories', 'planet4-master-theme')),
    ],
    gravityFormWithText(),
  ]],
]);

export default template;
