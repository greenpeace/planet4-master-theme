import { getMatchingVars } from './getMatchingVars';

const toLabel = element => {
  const idPart = !element.id ? '' : `#${ element.id }`;
  const classPart = typeof element.className !== 'string' ? '' : `.${ element.className.trim().replace(/ /g, '.') }`;

  return element.tagName.toLowerCase() + idPart + classPart;
};

export const byNameStateProp = ({name: nameA},{name: nameB}) => {
  const reg = /--(?<element>\w+(-?-\w+)*)(--(?<state>(active|focus|visited|hover|disabled)))?--(?<prop>\w+(-\w+)*)/;
  try {

    const {element: elementA, state: stateA, prop: propA } = nameA.match(reg).groups;
    const {element: elementB, state: stateB, prop: propB } = nameB.match(reg).groups;

    if (elementA !== elementB) {
      return elementA < elementB ? -1 : 1;
    }
    if (stateA !== stateB) {
      return stateA < stateB ? -1 : 1;
    }
    return propA < propB ? -1 : 1;
  } catch (e) {
    console.log(e)
    console.log('A', nameA, 'B', nameB);
    return nameA > nameB;
  }
}

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
        vars: previousMatches.filter(match => !currentMatches.includes(match)).sort(byNameStateProp),
      });
      previousMatches = currentMatches;
    }

    previous = current;
  }

  return groups;
}

