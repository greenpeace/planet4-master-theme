import { compare } from 'specificity';

const pseudoRegex = /(:(hover|focus|active|disabled|visited))/g;

const getMaxMatchingSpecificity = (usages, element) => {
  return usages.reduce((max, usage) => {
    if (!usage) {
      return max;
    }
    if (!element.matches(usage.selector)) {
      return max;
    }
    if (max === null) {
      return usage;
    }
    try {
      if (compare(max.selector, usage.selector) === -1) {
        return usage;
      }
    } catch (e) {
      console.log(e);
      return usage;
    }
    return max;
  }, null);
}

const groupByMediaQueries = (all, usage) => {
  const mediaKey = usage.media || 'all';
  const prevUsages = all[mediaKey] || [];
  const allUsages = [...prevUsages, usage]
  return ({
    ...all,
    [mediaKey]: allUsages,
  });
};

export const getOnlyMostSpecific = (vars, element) => {
  // Reduce to an object, then back to array. Easier to work with for this purpose.
  const asObject = vars.reduce((all, current)=> {
    const byMediaQueries = current.usages.reduce(groupByMediaQueries, {});

    Object.entries(byMediaQueries).forEach(([media,usages]) => {
      const maxSpecific = getMaxMatchingSpecificity(usages, element) || usages[0];
      // Won't have anything added if it doesn't match
      const pseudoSuffix = (maxSpecific.selector.split(',')[0].match(pseudoRegex) || []).join('')
      const propName = usages[0].property + pseudoSuffix + media;

      if (!all[propName]) {
        all[propName] = {...current, maxSpecific};
      } else {
        const comparedUsage = getMaxMatchingSpecificity([maxSpecific, all[propName].maxSpecific], element);
        if (maxSpecific === comparedUsage) {
          all[propName] = {...current, maxSpecific};
        }
      }

    })
    return all;
  },{});
  // Map back to array.
  return Object.entries(asObject).map(([, v]) => v);
}


export const filterMostSpecific = (groups) => {
  return groups.map(({ vars, element, ...other }) => ({
    ...other,
    element,
    vars: getOnlyMostSpecific(vars, element),
  }));
};
