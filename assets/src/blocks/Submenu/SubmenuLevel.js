import { Component } from '@wordpress/element';
import {
  CheckboxControl,
  SelectControl,
} from '@wordpress/components';

const { __ } = wp.i18n;

const getHeadingOptions = (minLevel) => {
  return [
    { label: __('None', 'planet4-blocks'), value: 0 },
    ...[2, 3, 4, 5, 6].map(n => ({
      label: __('Heading %n', 'planet4-blocks').replace('%n', n),
      value: n,
      disabled: n <= minLevel,
    }))
  ];
};

export class SubmenuLevel extends Component {
  render() {
    const {
      index,
      heading,
      onLinkChange,
      link,
      onHeadingChange,
      style,
      onStyleChange,
      minLevel,
    } = this.props;

    return (
      <div>
        <p>{`${__('Level', 'planet4-blocks')} ${Number(index + 1)}`}</p>
        <SelectControl
          label={__('Submenu item', 'planet4-blocks')}
          value={heading}
          options={getHeadingOptions(minLevel)}
          onChange={e => onHeadingChange(index, e)}
        />

        <CheckboxControl
          label={__('Link', 'planet4-blocks')}
          value={link}
          checked={link}
          onChange={e => onLinkChange(index, e)}
          className="submenu-level-link"
        />

        <SelectControl
          label={__('List style', 'planet4-blocks')}
          value={style}
          options={[
            { label: __('None', 'planet4-blocks'), value: 'none' },
            { label: __('Bullet', 'planet4-blocks'), value: 'bullet' },
            { label: __('Number', 'planet4-blocks'), value: 'number' },
          ]}
          onChange={e => onStyleChange(index, e)}
        />
        <hr />
      </div>
    );
  };
}
