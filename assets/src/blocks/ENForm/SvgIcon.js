export const SvgIcon = (props) => {
  const theme_dir = '/wp-content/themes/planet4-master-theme';
  const {
    name,
    sprite = `${theme_dir}/images/symbol/svg/sprite.symbol.svg`,
    class_name = 'icon',
  } = props;

  return (
    <svg viewBox="0 0 32 32" className={ class_name }>
      <use xlinkHref={ `${sprite}#${name}` } />
    </svg>
  )
}
