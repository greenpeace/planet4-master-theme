// Following is needed to get the right global object in all environments.
// See https://stackoverflow.com/a/6930376/4961158.
let global;
try {
  global = Function('return this')();
} catch(e) {
  global = window;
}

// A deep clone that has the same result as doing JSON.parse(JSON.stringify(value)).
// It's 2 to 5 times faster than both JSON switcheroo and lodash's cloneDeep in most cases.
// Only for really large object sizes, e.g. 500KB and over it's roughly the same or a little slower than Lodash.
// It does not preserve the object in the same way as Lodash does, i.e. non-built in classes are not preserved, just
// like they aren't in the JSON switcheroo approach. So it's only intended to be used where you would otherwise be able
// to use the JSON approach.
const _deepClone = (value, ancestors = [], clones = []) => {
  const type = typeof value;
  if (type === 'function') {
    return undefined;
  }
  if (type !== 'object') {
    return value;
  }
  if (ancestors.includes(value)) {

    return clones[ancestors.indexOf(value)];
  }
  if (Array.isArray(value)) {
    const cloned = value.map(v => _deepClone(v, ancestors, clones));
    ancestors.push(value);
    clones.push(cloned);
    if (value.hasOwnProperty('index')) {
      cloned.index = value.index;
    }
    if (value.hasOwnProperty('input')) {
      cloned.input = value.input;
    }

    return cloned;
  }

  const valueOf = value.valueOf();

  // If there is a valueOf and it's not an object, we need to pass it to the new constructor to get a new object with
  // the same value. Needed for Date, also makes Boolean objects work (even though you shouldn't use them).
  const param = typeof valueOf === 'object' ? null : valueOf;
  // Don't try to construct custom objects, use Object instead, which behaves the same as the JSON approach.
  let constructor = global[value.constructor.name] || Object;
  const newObject = new constructor(param);

  ancestors.push(value);
  clones.push(newObject);

  Object.keys(value).forEach(k => newObject[k] = _deepClone(value[k], ancestors, clones));

  return newObject;
}

export const deepClone = value => _deepClone(value);
