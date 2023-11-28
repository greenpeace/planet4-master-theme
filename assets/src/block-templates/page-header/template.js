import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const template = ({
  mediaPosition = '',
  imageFill = false,
  titlePlaceholder = __('Enter title', 'planet4-blocks-backend'),
}) => ([
  ['core/group', {align: 'full'},
    [
      ['core/group', {className: 'container'}, [
        ['core/media-text', {
          mediaType: 'image',
          mediaPosition,
          imageFill,
          className: 'is-pattern-p4-page-header is-style-parallax',
          mediaUrl: `${mainThemeUrl}/images/placeholders/placeholder-546x415.jpg`,
          isStackedOnMobile: true,
          align: 'full',
        }, [
          ['core/group', {}, [
            ['core/heading', {
              level: 1,
              backgroundColor: 'white',
              placeholder: titlePlaceholder,
            }],
          ]],
          ['core/paragraph', {
            placeholder: __('Enter description', 'planet4-blocks-backend'),
            style: {typography: {fontSize: '1.25rem'}},
          }],
          ['core/buttons', {}, [
            ['core/button', {className: 'is-style-cta'}],
          ]],
        ]],
      ]],
    ],
  ],
]);

export default template;
