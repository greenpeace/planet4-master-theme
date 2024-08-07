// useScript implementation from: https://usehooks.com/useScript/
import {addScriptTag} from './addScriptTag';

const {useEffect, useState} = wp.element;

export const useScript = (src, onScriptLoaded, deps = []) => {
  // Keeping track of script loaded and error state
  const [state, setState] = useState({
    loaded: false,
    error: false,
  });

  useEffect(
    () => {
      if (!!document.querySelector(`script[src="${src}"]`)) {
        setState({
          loaded: true,
          error: false,
        });

        return;
      }

      // Script event listener callbacks for load and error
      const onScriptLoad = () => {
        setState({
          loaded: true,
          error: false,
        });

        if (typeof onScriptLoaded === 'function') {
          onScriptLoaded();
        }
      };

      const onScriptError = () => {
        setState({
          loaded: true,
          error: true,
        });
      };

      const script = addScriptTag({
        src,
        async: true,
        onLoad: onScriptLoad,
        onError: onScriptError,
      });

      // Remove event listeners on cleanup
      return () => {
        script.removeEventListener('load', onScriptLoad);
        script.removeEventListener('error', onScriptError);
      };
    },
    deps
  );

  return [state.loaded, state.error];
};

export const removeScript = (src, deps = []) => {
  const [state, setState] = useState({unloaded: false});

  useEffect(
    () => {
      [...document.querySelectorAll(`script[src="${src}"]`)].forEach(node => {
        node.remove();
      });
      setState({unloaded: true});
    },
    deps
  );

  return [state.unloaded];
};

