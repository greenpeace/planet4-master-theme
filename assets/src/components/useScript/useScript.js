// useScript implementation from: https://usehooks.com/useScript/
import { useEffect, useState } from 'react';

export const useScript = (src) => {
  // Keeping track of script loaded and error state
  const [state, setState] = useState({
    loaded: false,
    error: false
  });

  useEffect(
    () => {

      if (!!document.querySelector(`script[src="${src}"]`)) {
        setState({
          loaded: true,
          error: false
        });

        return;
      }

      // Create script
      const script = document.createElement('script');
      script.src = src;
      script.async = true;

      // Script event listener callbacks for load and error
      const onScriptLoad = () => {
        setState({
          loaded: true,
          error: false
        });
      };

      const onScriptError = () => {
        script.remove();

        setState({
          loaded: true,
          error: true
        });
      };

      script.addEventListener('load', onScriptLoad);
      script.addEventListener('error', onScriptError);

      // Add script to document body
      document.body.appendChild(script);

      // Remove event listeners on cleanup
      return () => {
        script.removeEventListener('load', onScriptLoad);
        script.removeEventListener('error', onScriptError);
      };
    },
    []
  );

  return [state.loaded, state.error];
}
