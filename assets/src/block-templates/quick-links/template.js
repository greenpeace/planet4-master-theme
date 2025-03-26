import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const category = ['core/column', {}, [
  ['core/group', {className: 'group-stretched-link'}, [
    ['core/image', {
      align: 'center',
      className: 'is-style-small-circle force-no-lightbox force-no-caption mb-0',
      width: '90px',
      height: '90px',
      url: `${mainThemeUrl}/images/placeholders/placeholder-90x90.jpg`,
    }],
    ['core/spacer', {height: '16px'}],
    ['core/heading', {
      level: 5,
      style: {typography: {fontSize: '1rem'}},
      textAlign: 'center',
      placeholder: __('Category', 'planet4-blocks-backend'),
    }],
  ]],
]];

const template = ({
  title = '',
  backgroundColor = 'beige-100',
}) => ([
  ['core/group', {
    className: 'block',
    align: 'full',
    backgroundColor,
  }, [
    ['core/group', {className: 'container'}, [
      ['core/spacer', {height: '24px'}],
      ['core/heading', {level: 4, placeholder: __('Enter title', 'planet4-blocks-backend'), content: title}],
      ['core/columns', {
        isStackedOnMobile: false,
        className: 'is-style-mobile-carousel',
      },
      [...Array(5).keys()].map(() => category)],
    ]],
  ]],
]);

export default template;
