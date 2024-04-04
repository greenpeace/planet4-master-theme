
export const inputId = field => {
  switch (field.en_type) {
  case 'GEN':
  case 'OPT':
    return {
      id: `en__field_supporter_questions_${field.id}`,
      name: `supporter.questions.${field.id}`,
    };
  case 'Field':
  default:
    return {
      id: `en__field_supporter_${field.property}`,
      name: `supporter.${field.property}`,
    };
  }
};
