import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const template = ({
  titlePlaceholder = __('Enter text', 'planet4-blocks-backend'),
}) => [
  [
    'core/columns',
    {
      className: 'block',
      textColor: 'white',
      backgroundColor: 'dark-green-800',
    },
    [
      ['core/column', {}, [
        ['core/image', {
          align: 'center',
          className: 'force-no-lightbox force-no-caption',
          url: `${mainThemeUrl}/images/placeholders/placeholder-80x80.jpg`,
        }],
        ['core/heading', {
          textAlign: 'center',
          level: 3,
          placeholder: titlePlaceholder,
        }],
        ['core/spacer', {height: '16px'}],
        ['core/buttons', {layout: {type: 'flex', justifyContent: 'center'}}, [
          ['core/button', {className: 'is-style-transparent'}],
        ]],
        ['core/spacer', {height: '16px'}],
      ]],
    ],
  ],
];

export default template;
