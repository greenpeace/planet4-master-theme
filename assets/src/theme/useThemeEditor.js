import { useReducer, useEffect } from 'react';
import { LOCAL_STORAGE_KEY } from './VarPicker';
import { useHotkeys } from 'react-hotkeys-hook';
import { byNameStateProp } from './groupVars';

const PROP_REGEX = /\w+(-\w+)*$/;
export const PSEUDO_REGEX = /--?(active|focus|visited|hover|disabled)--?/;

const write = (prop, value) => {
  document.documentElement.style.setProperty(prop, value);
};
const unset = prop => {
  return document.documentElement.style.removeProperty(prop);
};

const pushHistory = (history, entry) => {
  return [
    entry,
    // Slice to preserve up to 1001 last entries to have some upper limit.
    ...history.slice(-1000),
  ];
};

const lastWritten = {};
const keysToRemove = {};

const DEFAULT_STATE = {
  theme: {},
  history: [],
  future: [],
  defaultValues: {},
  previewProps: {},
  previewPseudoVars: {},
  lastSet: {},
};

const dropProps = (fromState, toState, previewProps, previewPseudoVars) => {
  return Object.keys(fromState).filter(k => {
    if (Object.keys(previewProps).includes(k)) {
      return false;
    }

    if (Object.keys(previewPseudoVars).some(pseudo => k.includes(pseudo))) {
      return false;
    }

    return !Object.keys(toState).includes(k);
  }).forEach(k => keysToRemove[k] = true);
};

const ACTIONS = {
  SET: (state, { name, value }) => {
    const { theme } = state;
    // In case this action is dispatched but the value doesn't change,
    const isActualSet = theme[name] !== value;

    if (theme[name] === value) {
      return state;
    }

    const shouldAddEntry = !state.lastSet[name] || Date.now() - state.lastSet[name] > 700;

    return {
      ...state,
      theme: { ...state.theme, [name]: value },
      history: !shouldAddEntry ? state.history : pushHistory(state.history, state.theme),
      lastSet: { ...state.lastSet, [name]: Date.now(), }
    };
  },
  UNSET: (state, { name }) => {
    const {
      [name]: oldValue,
      ...others
    } = state.theme;

    const isActualUnset = state.theme.hasOwnProperty(name);

    if (isActualUnset) {
      keysToRemove[name] = true;
    }

    return {
      ...state,
      theme: others,
      history: !isActualUnset ? state.history : pushHistory(state.history, state.theme),
    };
  },
  START_PREVIEW: (state, { name, value }) => {
    return {
      ...state,
      previewProps: {
        ...state.previewProps,
        [name]: value,
      }
    };
  },
  END_PREVIEW: (state, { name }) => {
    const {
      [name]: previewedValue,
      ...otherProps
    } = state.previewProps;

    if (
      !state.theme.hasOwnProperty(name)
      && !Object.keys(state.previewPseudoVars).map(s => s.replace(PSEUDO_REGEX, '--')).includes(name)) {
      keysToRemove[name] = true;
    }
    return {
      ...state,
      previewProps: { ...otherProps },
    };
  },
  START_PREVIEW_PSEUDO_STATE: (state, { name }) => {
    const element = name.replace(PSEUDO_REGEX, '--').replace(/\w+(-\w+)*$/, '');

    const pseudoState = (name.match(PSEUDO_REGEX) || [null])[0];

    return {
      ...state,
      previewPseudoVars: {
        ...state.previewPseudoVars,
        [element]: pseudoState,
      },
    };
  },
  END_PREVIEW_PSEUDO_STATE: (state, { name }) => {
    const elementToEnd = name.replace(PSEUDO_REGEX, '--').replace(PROP_REGEX, '');

    const {
      [elementToEnd]: discard,
      ...otherPseudos
    } = state.previewPseudoVars;

    Object.keys(state.defaultValues).forEach(k => {
      const withoutElement = k.replace(elementToEnd, '');

      if (withoutElement.replace(PROP_REGEX, '') !== '') {
        return;
      }

      if (!state.theme.hasOwnProperty(k)) {
        keysToRemove[k] = true;
      }
      // Unset the regular property so that it gets set again.
      lastWritten[k] = null;
    });

    return {
      ...state,
      previewPseudoVars: otherPseudos,
    };
  },
  HISTORY_BACKWARD: (state) => {
    const { theme, history, future, previewProps, previewPseudoVars } = state;

    if (history.length === 0) {
      return state;
    }
    const [prevTheme, ...older] = history;
    dropProps(theme, prevTheme, previewProps, previewPseudoVars);

    return {
      ...state,
      theme: prevTheme,
      history: older,
      future: [
        theme,
        ...future,
      ],
    };
  },
  HISTORY_FORWARD: (state) => {
    const { theme, history, future, previewProps, previewPseudoVars } = state;
    if (future.length === 0) {
      return state;
    }

    const [nextTheme, ...newer] = future;
    dropProps(theme, nextTheme, previewProps, previewPseudoVars);

    return {
      ...state,
      theme: nextTheme,
      future: newer,
      history: [
        theme,
        ...history
      ]
    };
  },
  LOAD_THEME: ({ defaultValues, history, theme: oldTheme }, { theme }) => {
    dropProps(oldTheme, theme, {}, {});
    Object.keys(lastWritten).forEach(k => lastWritten[k] = null);
    Object.keys(theme).forEach(k => keysToRemove[k] = true);

    return {
      ...DEFAULT_STATE,
      defaultValues,
      theme,
      history: pushHistory(history,oldTheme),
    };
  }
};

// Experimenting with exporting the reducers themselves as ACTION keys. If you pass a function to the "main" reducer,
// it will use the name of the function to locate the action. This has some benefits: no need to have a separate ACTIONS
// constant each time you want to use a reducer, and need to update it each time you add a new action. It also makes it
// easier to navigate from the dispatch directly to the function that handles it. This can be easily switched back to
// strings if needed by changing the import only.
export const THEME_ACTIONS = ACTIONS;

function reducer(state, { type, payload }) {
  console.log('Received action', type, 'with payload', payload);

  const action = typeof type === 'function' ? type.name : type;

  if (typeof ACTIONS[action] !== 'function') {
    throw new Error(`No handler for action ${ action }`);
  }

  return ACTIONS[action](state, payload);
}

const writeNewValues = theme => {
  Object.keys(theme).forEach((k) => {
    // This will work as long as nothing else is setting the same properties. Perhaps there's no cost to setting
    // properties to the same value and this can be simplified?
    if (lastWritten[k] === theme[k]) {
      return;
    }

    write(k, theme[k]);
    lastWritten[k] = theme[k];
  });
};

const processRemovals = (defaultValues) => {
  Object.keys(keysToRemove).forEach(k => {
    if (defaultValues[k]) {
      write(k, defaultValues[k]);
      lastWritten[k] = defaultValues[k];
    } else {
      unset(k);
      delete lastWritten[k];
    }
    delete keysToRemove[k];
  });
};

const getAllDefaultValues = allVars => {
  const fromAvailableVars = allVars.reduce((values, cssVar) => {
    // All should use the default value, but can't be guaranteed. If they're different it's hard to tell which it
    // should be. Just use the first one. No usages is not possible in theory but check existence anyway.
    const supposedDefaultValue = cssVar.usages[0]?.defaultValue;

    return {
      ...values,
      [cssVar.name]: supposedDefaultValue,
    };
  }, {});

  return {
    ...fromAvailableVars,
    // ...atRuntime,
  };
};

const applyPseudoPreviews = (defaultValues, resolvedValues, previewPseudoVars) =>
  Object.keys(defaultValues).reduce((values, k) => {

    const withoutProperty = k.replace(PROP_REGEX, '').replace(/-+$/, '');
    let elementState = previewPseudoVars[withoutProperty + '--'];

    if (!elementState) {
      return values;
    }

    elementState = elementState.replace(/-/g, '');
    const propName = (k.match(PROP_REGEX) || [null])[0];

    const varToPreview = Object.keys(defaultValues).find(k => {
      const lastPart =
        k
          .replace(withoutProperty, '')
          .replace(/^-+/, '');

      if (!lastPart.startsWith(elementState)) {
        return false;
      }
      const defaultProperty =
        lastPart
          .replace(`${ elementState }--`, '')
          .replace(/^-+/, '');

      return defaultProperty === propName;
    });

    if (!varToPreview) {
      return values;
    }

    const tmpValue = values[varToPreview] || defaultValues[varToPreview];

    // Set the regular property to what the pseudo element's value is.
    return {
      ...values,
      [k]: tmpValue,
    };
  }, resolvedValues)

export const useThemeEditor = (
  {
    initialState = DEFAULT_STATE,
    // baseTheme = null,
    allVars,
  }) => {
  // Read
  const [{
    theme,
    defaultValues,
    previewProps,
    previewPseudoVars,
    history,
    future,
  }, dispatch] = useReducer(reducer, initialState, s => ({
    ...s,
    defaultValues: getAllDefaultValues(allVars),
    theme: JSON.parse(localStorage.getItem(LOCAL_STORAGE_KEY) || '{}'),
  }));

  const resolvedValues = {
    ...theme,
    ...previewProps,
  };

  const withPseudoPreviews = previewPseudoVars.length === 0
    ? resolvedValues
    : applyPseudoPreviews(defaultValues, resolvedValues, previewPseudoVars);

  useEffect(() => {
    processRemovals(defaultValues);
    writeNewValues(withPseudoPreviews);
    localStorage.setItem('theme-with-previews', JSON.stringify(withPseudoPreviews));
  }, [JSON.stringify(withPseudoPreviews)]);

  const hotkeysOptions = {
    enableOnTags: ['INPUT'],
  }

  useHotkeys('ctrl+z,cmd+z', () => {
    dispatch({type: THEME_ACTIONS.HISTORY_BACKWARD});
  }, hotkeysOptions);

  useHotkeys('ctrl+shift+z,cmd+shift+z', () => {
    dispatch({type: THEME_ACTIONS.HISTORY_FORWARD});
  }, hotkeysOptions);

  const sorted = Object.keys(theme).sort(
    (a, b) => byNameStateProp({ name: a }, { name: b })
  ).reduce((t, k) => ({ ...t, [k]: theme[k] }), {});

  const themeJson = JSON.stringify(sorted);
  useEffect(() => {
    localStorage.setItem(LOCAL_STORAGE_KEY, themeJson);
  }, [themeJson]);

  return [
    {
      theme,
      defaultValues,
      hasHistory: history.length > 0,
      hasFuture: future.length > 0,
    },
    dispatch,
  ];
};
