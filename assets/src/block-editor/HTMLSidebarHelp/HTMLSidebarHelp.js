import {RawHTML} from '@wordpress/element';

export const HTMLSidebarHelp = props => (
  <div className="HTMLSidebarHelp">
    <RawHTML>
      { props.children }
    </RawHTML>
  </div>
);
