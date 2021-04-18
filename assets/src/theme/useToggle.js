import { useState, useCallback} from 'react';

export const useToggle = (initialState = false) => {
  // Initialize the state
  const [state, setState] = useState(initialState);
  const toggle = useCallback(() => setState(state => !state), []);

  return [state, toggle]
}
