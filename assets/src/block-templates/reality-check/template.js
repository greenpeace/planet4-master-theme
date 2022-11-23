import mainThemeUrl from '../main-theme-url';

const column = ['core/column', {}, [
  ['core/group', {}, [
    ['core/image', {
      align: 'center',
      className: 'mb-0 force-no-lightbox force-no-caption',
      url: `${mainThemeUrl}/images/placeholders/placeholder-75x75.jpg`
    }],
    ['core/heading', {
      style: {typography: {fontSize: '4rem'}},
      textAlign: 'center',
      placeholder: __('Enter title', 'planet4-blocks-backend')
    }],
    ['core/paragraph', {
      align: 'center',
      placeholder: __('Enter description', 'planet4-blocks-backend')
    }],
    ['core/spacer', {height: '16px'}],
  ]]
]];

const template = () => ([
  ['core/columns', {
    className: 'block'
  }, [
    column, column, column
  ]]
]);

export default template;
