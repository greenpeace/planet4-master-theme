
export const inputName = (field) => {
  switch (field.en_type) {
    case 'GEN':
    case 'OPT':
    return `supporter.questions.${field.id}`;
    case 'Field':
    default:
    return `supporter.${field.property}`;
  }
};
