const {useDispatch, useSelect} = wp.data;

export const getSidebarFunctions = () => {
  const meta = useSelect(select => select('core/editor').getEditedPostAttribute('meta'), []);

  const {editPost} = useDispatch('core/editor');

  const updateValueAndDependencies = fieldId => value => editPost({meta: {[fieldId]: value}});

  const getParams = name => ({
    value: meta[name] || '',
    setValue: updateValueAndDependencies(name),
  });

  const getImageParams = (idField, urlField) => ({
    value: {
      id: meta[idField] || '',
      url: meta[urlField] || '',
    },
    setValue: (id, url) => {
      updateValueAndDependencies(idField)(id);
      updateValueAndDependencies(urlField)(url);
    },
  });

  return {
    getImageParams,
    getParams,
  };
};
