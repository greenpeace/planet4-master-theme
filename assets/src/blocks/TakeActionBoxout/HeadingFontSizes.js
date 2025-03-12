const {__} = wp.i18n;

export const FONT_SIZES = [
  {
    name: __('Small'),
    slug: 'small',
    size: '1.125rem',
  },
  {
    name: __('Medium'),
    slug: 'medium',
    size: '1.25rem',
  },
  {
    name: __('Large'),
    slug: 'large',
    size: '1.375rem',
  },
  {
    name: __('Extra Large'),
    slug: 'x-large',
    size: '1.75rem',
  },
];

export const getHeadingFontSizeClassName = fontSize => {
  if (!fontSize || !FONT_SIZES.find(font => font.size === fontSize)) {
    return 'medium';
  }

  return FONT_SIZES.find(font => font.size === fontSize).slug;
};
