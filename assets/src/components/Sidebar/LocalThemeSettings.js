import { SelectControl } from '@wordpress/components';
import { useState, useEffect } from 'react';
import { p4ServerThemes } from '../../theme/p4ServerThemes';
import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';

const keysAsLabel = obj => Object.keys(obj).map(k => ({ label: k, value: k }));

const refactoredThemes = ['climate-new', 'forest-new', 'oceans-new', 'plastic-new'];

// Just a lightweight way to have a separate UI element in the sidebar so that it's clear which themes are legacy and
// which are new. This is likely just for internal usage to facilitate reviewing the refactored version, eventually
// editors will work with a single select.
const useServerThemes = () => {
  const [serverThemes, setServerThemes] = useState({});

  useEffect(() => {
    (async () => {
      const themes = await p4ServerThemes.fetchThemes();
      setServerThemes(themes);
    })();
  }, []);

  return serverThemes;
};

const getAllDefinedProps = () => Object.values(document.documentElement.style).filter(k => {
  return 'string' === typeof k && k.match(/^--/);
});
const baseUrl = window.location.href.split( '/wp-admin' )[ 0 ];
export const themeJsonUrl = `${ baseUrl }/wp-content/themes/planet4-master-theme/themes/`;

const collectTheme = async (a, t) => {
  const response = await fetch(`${ themeJsonUrl + t.replace('-new', '') }.json`);

  return {
    ...await a,
    [t]: await response.json(),
  };
}

const useJsonThemes = () => {
  const [jsonThemes, setJsonThemes] = useState({});
  useEffect(() => {
    (async () => {
      const themes = await refactoredThemes.reduce(collectTheme, {});
      setJsonThemes(themes);
    })();
  }, []);

  return jsonThemes;
}

export const applyChangesToDom = (theme, initialVars) => {
  if (!theme) {
    return;
  }
  Object.entries(theme).forEach(([name, value]) => {
    // This will only work reliably if no other code is adding new custom properties to the root element after this
    // component is first rendered. This should be the case in the post editor.
    document.documentElement.style.setProperty(name, value);
  });

  const customProps = getAllDefinedProps();

  customProps.forEach(k => {
    if (!Object.keys(theme).includes(k) && !initialVars.includes(k)) {
      document.documentElement.style.removeProperty(k);
    }
  });
};

const useAppliedCssVariables = (serverThemes, currentTheme) => {
  const [initialVars] = useState(() => getAllDefinedProps(), []);
  const jsonThemes = useJsonThemes();
  const allThemes = { ...serverThemes, ...jsonThemes };

  useEffect(() => {
    applyChangesToDom(allThemes[currentTheme] || {}, initialVars);
  }, [serverThemes, currentTheme]);
};

const excludeNewVersions = (themes, [name, theme]) => {
  return refactoredThemes.includes(name) ? themes : ({ ...themes, [name]: theme });
};

const withoutNewVersionsOfThemes = themes => Object.entries(themes).reduce(excludeNewVersions, {});

export const LocalThemeSettings = ({ onChange, currentTheme }) => {
  const [selectedTheme, setSelectedTheme] = useState(currentTheme);
  const {editPost} = useDispatch('core/editor');
  const allServerThemes = useServerThemes();
  const serverThemes = withoutNewVersionsOfThemes(allServerThemes);

  const emitOnChange = () => {
    if (selectedTheme !== null) {
      const meta = { theme: selectedTheme };
      onChange(selectedTheme);
      editPost({meta});
    }
  };
  useEffect(emitOnChange, [selectedTheme]);

  useAppliedCssVariables(serverThemes, currentTheme);

  if (Object.keys(serverThemes).length === 0) {
    return null;
  }

  return <div className="components-panel__body is-opened">
    <span>
      { __('Choose from one of the themes created on this site (BETA).', 'planet4-blocks-backend') }
    </span>
    <SelectControl
      label={ __('Local theme', 'planet4-blocks-backend') }
      title={ __('Choose from one of the themes created on this site (BETA).', 'planet4-blocks-backend') }
      options={ [{ label: 'Legacy', value: '' }, ...keysAsLabel(serverThemes)] }
      onChange={ setSelectedTheme }
      value={ selectedTheme || '' }
    />
  </div>;
};
