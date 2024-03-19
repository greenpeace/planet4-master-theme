// useScript implementation from: https://usehooks.com/useScript/
import {useEffect, useState} from '@wordpress/element';
import {addLinkTag} from './addLinkTag';

export const useStyleSheet = href => {
  // Keeping track of script loaded and error state
  const [state, setState] = useState({
    loaded: false,
    error: false,
  });

  useEffect(
    () => {
      if (!!document.querySelector(`link[href="${href}"]`)) {
        setState({
          loaded: true,
          error: false,
        });

        return;
      }

      // Stylesheet event listener callbacks for load and error
      const onStyleSheetLoad = () => {
        setState({
          loaded: true,
          error: false,
        });
      };

      const onStyleSheetError = () => {
        linkElement.remove();

        setState({
          loaded: true,
          error: true,
        });
      };

      // Create stylesheet link
      const linkElement = addLinkTag({
        href,
        onLoad: onStyleSheetLoad,
        onError: onStyleSheetError,
      });

      // Remove event listeners on cleanup
      return () => {
        linkElement.removeEventListener('load', onStyleSheetLoad);
        linkElement.removeEventListener('error', onStyleSheetError);
      };
    },
    []
  );

  return [state.loaded, state.error];
};
