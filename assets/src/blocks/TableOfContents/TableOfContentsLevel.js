const {CheckboxControl, SelectControl} = wp.components;
const {__} = wp.i18n;

const getHeadingOptions = minLevel => {
  return [
    {label: __('Heading 2', 'planet4-master-theme-backend'), value: 2},
    {label: __('Heading 3', 'planet4-master-theme-backend'), value: 3},
    {label: __('Heading 4', 'planet4-master-theme-backend'), value: 4},
    {label: __('Heading 5', 'planet4-master-theme-backend'), value: 5},
    {label: __('Heading 6', 'planet4-master-theme-backend'), value: 6},
  ].map(option => ({...option, disabled: option.value <= minLevel}));
};

export const TableOfContentsLevel = props => {
  const {
    index,
    heading,
    onLinkChange,
    link,
    onHeadingChange,
    style,
    onStyleChange,
    minLevel,
  } = props;

  return (
    <div>
      <p>{`${__('Level', 'planet4-master-theme-backend')} ${Number(index + 1)}`}</p>
      <SelectControl
        __nextHasNoMarginBottom
        __next40pxDefaultSize
        label={__('Table of Contents item', 'planet4-master-theme-backend')}
        value={heading}
        options={getHeadingOptions(minLevel)}
        onChange={e => onHeadingChange(index, e)}
      />

      <CheckboxControl
        __nextHasNoMarginBottom
        label={__('Link', 'planet4-master-theme-backend')}
        value={link}
        checked={link}
        onChange={e => onLinkChange(index, e)}
        className="table-of-contents-level-link"
      />

      <SelectControl
        __nextHasNoMarginBottom
        __next40pxDefaultSize
        label={__('List style', 'planet4-master-theme-backend')}
        value={style}
        options={[
          {label: __('None', 'planet4-master-theme-backend'), value: 'none'},
          {label: __('Bullet', 'planet4-master-theme-backend'), value: 'bullet'},
          {label: __('Number', 'planet4-master-theme-backend'), value: 'number'},
        ]}
        onChange={e => onStyleChange(index, e)}
      />
      <hr />
    </div>
  );
};
