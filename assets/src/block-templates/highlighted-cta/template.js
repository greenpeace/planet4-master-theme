import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const template = ({
  titlePlaceholder = __('Enter text', 'planet4-master-theme-backend'),
}) => [
  [
    'core/group',
    {
      className: 'block',
      backgroundColor: 'dark-green-800',
    },
    [
      ['core/spacer', {height: '40px'}],
      ['core/image', {
        align: 'center',
        className: 'force-no-lightbox force-no-caption',
        url: `${mainThemeUrl}/images/placeholders/placeholder-80x80.jpg`,
        width: '80px',
        height: '80px',
        style: {
          spacing: {
            margin: {
              top: '0',
              bottom: '0',
            },
          },
        },
      }],
      ['core/spacer', {height: '32px'}],
      ['core/heading', {
        textAlign: 'center',
        level: 3,
        placeholder: titlePlaceholder,
        textColor: 'white',
      }],
      ['core/spacer', {height: '24px'}],
      ['core/buttons', {layout: {type: 'flex', justifyContent: 'center'}}, [
        ['core/button', {className: 'is-style-cta'}],
      ]],
      ['core/spacer', {height: '40px'}],
    ],
  ],
];

export default template;
