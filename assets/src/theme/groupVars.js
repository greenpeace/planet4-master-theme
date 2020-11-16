import { getMatchingVars } from './getMatchingVars';

const toLabel = element => {
  const idPart = !element.id ? '' : `#${ element.id }`;
  const classPart = !element.className ? '' : `.${ element.className.trim().replace(/ /g, '.') }`;

  return element.tagName.toLowerCase() + idPart + classPart;
};

const parseVarName = name => {
  const parts = name.split('--');

  const reversed = parts.reverse();

  return [reversed.slice(1).join('--'), reversed[0]];
}

const byVarName= ({name: nameA}, {name: nameB}) => {
  const [prefixA, propA] = parseVarName(nameA);
  const [prefixB, propB] = parseVarName(nameB);

  if (prefixA === prefixB) {
    return propA > propB ? 1 : -1;
  }

  return prefixA > prefixB ? 1 : -1;
}

const byPropertyThenName = (a, b) => {
  const { usages: usagesA, name: nameA } = a;
  const { usages: usagesB, name: nameB } = b;

  // Secondary sort if property is the same.
  if (usagesA[0].property === usagesB[0].property) {
    const [prefixA] = parseVarName(nameA);
    const [prefixB] = parseVarName(nameB);

    return prefixA > prefixB ? 1 : -1;
  }

  return usagesA[0].property > usagesB[0].property ? 1 : -1;
};

export const groupVars = async (vars, target) => {
  const groups = [];
  let current,
    previous = target,
    previousMatches = vars;

  // Walk up the tree to the root to assign each variable to the highest element they apply to.
  while (current = previous.parentNode) {
    if (previousMatches.length === 0) {
      break;
    }
    // Get the matching vars and compare them to the previous matches. Any ones that don't occur for this element are
    // therefore variables that apply because of the previous element.
    const currentMatches = await getMatchingVars({ cssVars: previousMatches, target: current });

    if (currentMatches.length < previousMatches.length) {
      groups.push({
        element: previous,
        label: toLabel(previous),
        vars: previousMatches.filter(match => !currentMatches.includes(match)).sort(byPropertyThenName),
      });
      previousMatches = currentMatches;
    }

    previous = current;
  }

  return groups;
}

