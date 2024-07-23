export const formFields = {
  fields: [
    {
      id: 28121,
      name: 'Email',
      tag: 'Email Address',
      property: 'emailAddress',
    },
    {
      id: 28116,
      name: 'First name',
      tag: 'First Name',
      property: 'firstName',
    },
    {
      id: 28117,
      name: 'Last name',
      tag: 'Last Name',
      property: 'lastName',
    },
    {
      id: 28122,
      name: 'Country',
      tag: 'Country',
      property: 'country',
    },
    {
      id: 67127,
      name: 'AwakenMe',
      tag: 'Not Tagged',
      property: 'NOT_TAGGED_1',
    },
  ],
  questions: [
    {
      id: 236734,
      questionId: 25781,
      name: 'test question 1',
      type: 'GEN',
    },
    {
      id: 220954,
      questionId: 25511,
      name: 'nro_data_ok',
      type: 'OPT',
    },
    {
      id: 3887,
      questionId: 3665,
      name: 'Opt-in',
      type: 'OPT',
    },
  ],
};

export const formFieldsAttributes = {
  'First name': {
    default_value: '',
    label: 'First Name',
    required: false,
    type: 'Text',
  },
  'Last name': {
    default_value: '',
    label: 'Last Name',
    required: false,
    type: 'Text',
  },
  Email: {
    default_value: '',
    label: 'Email',
    required: true,
    type: 'Email',
  },
  Country: {
    default_value: '',
    label: 'Country',
    required: false,
    type: 'Country',
  },
  AwakenMe: {
    default_value: 'hidden field ασφ (0287#$%^ 日本語',
    label: '',
    required: false,
    type: 'Hidden',
  },
  'test question 1': {
    default_value: '',
    label: 'What\'s the question?',
    required: false,
    type: 'Text',
  },
  nro_data_ok: {
    default_value: '',
    label: 'I am happy for my data to be shared with my local Greenpeace office.',
    required: false,
    type: 'Checkbox',
  },
  'Opt-in': {
    default_value: '',
    label: 'Opt in',
    required: false,
    type: 'Checkbox',
    dependency: 'nro_data_ok',
  },
};

