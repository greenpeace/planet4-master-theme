import {RawHTML} from '@wordpress/element';

export const HTMLSidebarHelp = props => {
  return <div className="HTMLSidebarHelp">
    <RawHTML>
      { props.children }
    </RawHTML>
  </div>;
};
