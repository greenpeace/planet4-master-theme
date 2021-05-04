const balancedVar = require('./balancedVar');

const isSameDomain = ({ href }) => !href || href.indexOf(window.location.origin) === 0;
const isNotCoreFile = ({ href }) => !href || !href.includes('wp-includes')

const collectRuleVars = (collected, rule, sheet, media = null, supports = null) => {
  if (rule.type === 1) {
    // Parse CSS text to get original declarations.
    const ruleBody = rule.cssText.trim().replace(/^.*{/, '').replace(/;?\s*}\s*$/, '');
    const decls = ruleBody.split(';').map(decl => decl.split(':'));

    decls.forEach(([propertyRaw, ...value]) => {
      const property = propertyRaw.trim();
      // Rejoin in case there could be more ":" inside of the value.
      let remainingValue = value.join(':');
      let match;
      while ( (match = balancedVar( remainingValue )) ) {
        // Split at the comma to find variable name and fallback value.
        const varArguments = match.body.split( ',' ).map( str => str.trim() );

        // There may be other commas in the values so this isn't necessarily just 2 pieces.
        // By spec everything after the first comma (including commas) is a single default value we'll join again.
        const [variableName, ...defaultValue] = varArguments;

        const usage = {
          selector: rule.selectorText,
          property,
          defaultValue: defaultValue.join(','),
          media,
          supports,
          sheet: sheet.href,
        }
        if (!(variableName in collected)) {
          collected[variableName] = { usages: [] };
        }
        collected[variableName].usages.push(usage);
        // Replace variable name (first occurrence only) from result, to avoid circular loop
        remainingValue = (match.pre || '') + match.body.replace(variableName, '') + (match.post || '');
      }
    })
    return collected;
  }
  if (rule.type === 4) {
    // No nested media queries for now.
    [...rule.cssRules].forEach(innerRule => collectRuleVars(collected, innerRule, sheet, rule.conditionText, supports))
    return collected;
  }
  if (rule.type === 12) {
    // No nested supports queries for now.
    [...rule.cssRules].forEach(innerRule => collectRuleVars(collected, innerRule, sheet, media, rule.conditionText));
    return collected;
  }

  // console.log(rule.type, rule);
  return collected;
}

const collectSheetVars = (vars, sheet) => {
  return [...sheet.cssRules].reduce((sheetVars, rule) => collectRuleVars(sheetVars, rule, sheet), vars);
};

export const extractPageVariables = async() => {
  const asObject = [...document.styleSheets].filter(isSameDomain).filter(isNotCoreFile).reduce(collectSheetVars, {});

  return Object.keys(asObject).sort().map((k) => {
    const v = asObject[k];
    return ({
      name: k,
      ...v,
    });
  })
};
