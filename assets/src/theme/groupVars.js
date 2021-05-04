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

    if (propA !== propB) {
      return propA < propB ? -1 : 1;
    }
    if (elementA !== elementB) {
      return elementA < elementB ? -1 : 1;
    }

    return stateA < stateB ? -1 : 1;
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

  // Walk up the tree to the root to assign each variable to the deepest element they apply to. Each time we go up we
  // test the remaining variables. If the current element doesn't match all anymore, the non matching are assigned to
  // the previous (one level deeper) element.
  while (current = previous.parentNode) {
    if (previousMatches.length === 0) {
      break;
    }
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

