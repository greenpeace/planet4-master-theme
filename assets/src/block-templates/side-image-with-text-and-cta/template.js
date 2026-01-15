import mainThemeUrl from '../main-theme-url';

const {__} = wp.i18n;

const template = ({
  title = '',
  backgroundColor = '',
  alignFull = false,
  mediaPosition = 'left',
}) => ([
  ['core/media-text', {
    mediaType: 'image',
    mediaPosition,
    mediaUrl: `${mainThemeUrl}/images/placeholders/placeholder-546x415.jpg`,
    isStackedOnMobile: true,
    backgroundColor,
    alignFull,
  }, [
    ['core/heading', {level: 2, placeholder: __('Enter title', 'planet4-master-theme-backend'), content: title}],
    ['core/paragraph', {placeholder: __('Enter description', 'planet4-master-theme-backend')}],
    ['core/buttons', {}, [
      ['core/button'],
    ]],
  ]],
]);

export default template;
