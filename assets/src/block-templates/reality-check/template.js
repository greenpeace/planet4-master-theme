import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const column = ['core/column', {}, [
  ['core/group', {}, [
    ['core/image', {
      align: 'center',
      className: 'mb-0 force-no-lightbox force-no-caption',
      url: `${mainThemeUrl}/images/placeholders/placeholder-75x75.jpg`,
    }],
    ['core/heading', {
      style: {typography: {fontSize: '4rem'}},
      level: 3,
      textAlign: 'center',
      placeholder: __('Enter title', 'planet4-master-theme-backend'),
    }],
    ['core/paragraph', {
      align: 'center',
      placeholder: __('Enter description', 'planet4-master-theme-backend'),
    }],
    ['core/spacer', {height: '16px'}],
  ]],
]];

const template = () => ([
  ['core/heading', {
    className: 'visually-hidden',
    level: 2,
    content: __('Reality check', 'planet4-master-theme-backend'),
  }],
  ['core/columns', {
    className: 'block',
  },
  [...Array(3).keys()].map(() => column),
  ],
]);

export default template;
