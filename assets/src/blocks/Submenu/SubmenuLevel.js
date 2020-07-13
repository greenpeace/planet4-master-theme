import { Component } from '@wordpress/element';
import {
  CheckboxControl,
  SelectControl,
} from '@wordpress/components';

const { __ } = wp.i18n;

export class SubmenuLevel extends Component {
  render() {
    const {
      index,
      heading,
      onLinkChange,
      link,
      onHeadingChange,
      style,
      onStyleChange
    } = this.props;

    return (
      <div>
        <p>{`${__('Level', 'planet4-blocks')} ${Number(index + 1)}`}</p>
        <SelectControl
          label={__('Submenu item', 'planet4-blocks')}
          value={heading}
          options={[
            { label: __('None', 'planet4-blocks'), value: 0 },
            { label: __('Heading 1', 'planet4-blocks'), value: 1 },
            { label: __('Heading 2', 'planet4-blocks'), value: 2 },
            { label: __('Heading 3', 'planet4-blocks'), value: 3 },
            { label: __('Heading 4', 'planet4-blocks'), value: 4 },
            { label: __('Heading 5', 'planet4-blocks'), value: 5 },
            { label: __('Heading 6', 'planet4-blocks'), value: 6 },
          ]}
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
