import gravityFormWithText from '../templates/gravity-form-with-text';
import {getPostListBlockTemplate, POSTS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/PostsList';
import {getActionsListBlockTemplate, ACTIONS_LIST_BLOCK_ATTRIBUTES} from '../../blocks/ActionsList';

const {__} = wp.i18n;

const template = () => (
  [
    ['core/group', {}, [
      ['planet4-blocks/carousel-header'],
      ['planet4-block-templates/issues', {
        title: __('The issues we work on', 'planet4-blocks'),
      }],
      ['core/spacer', {height: '88px'}],
      ['core/query',
        POSTS_LIST_BLOCK_ATTRIBUTES,
        getPostListBlockTemplate(__('Read our Stories', 'planet4-blocks')),
      ],
      ['core/spacer', {height: '56px'}],
      ['planet4-block-templates/side-image-with-text-and-cta', {
        title: __('Get to know us', 'planet4-blocks'),
      }],
      ['core/spacer', {height: '30px'}],
      ['planet4-block-templates/side-image-with-text-and-cta', {
        title: __('We win campaigns', 'planet4-blocks'),
        mediaPosition: 'right',
      }],
      ['core/spacer', {height: '56px'}],
      ['core/query',
        ACTIONS_LIST_BLOCK_ATTRIBUTES,
        getActionsListBlockTemplate(),
      ],
      ['core/spacer', {height: '72px'}],
      gravityFormWithText(),
    ]],
  ]
);

export default template;
