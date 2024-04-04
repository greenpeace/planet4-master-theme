export const PositionSelector = attributes => {
  const {
    name = 'position-selector',
    id = null,
    default_text = '',
    class_name = '',
    error_message = '',
    required = false,
    onInputChange = null,
    onBlur = null,
  } = attributes;

  const options = [
    <option key="default" value="" disabled={true}>{ default_text }</option>,
    ...positions.map(p => {
      return <option key={p.code} value={p.code}>{ p.name }</option>;
    }),
  ];

  return (
    <select
      id={id}
      name={name}
      className={class_name}
      data-errormessage={error_message}
      required={required}
      defaultValue=""
      onChange={onInputChange}
      onBlur={onBlur}
    >
      { options }
    </select>
  );
};

const positions = [
  {code: 'politician', name: 'Politician / Political figure'},
  {code: 'scientist', name: 'Scientist / Academic'},
  {code: 'business_leader', name: 'Business leader / Business'},
  {code: 'indigenous_leader', name: 'Indigenous leader or organisation'},
  {code: 'artists', name: 'Artists'},
  {code: 'faith_leader', name: 'Faith leader / Faith community'},
  {code: 'civil_society_leader', name: 'Civil society leader or organisation'},
  {code: 'minister', name: 'Minister or former Minister'},
  {code: 'cultural_leader', name: 'Cultural leader or organisation'},
  {code: 'youth_leader', name: 'Youth leader or organisation'},
  {code: 'unions', name: 'Unions'},
  {code: 'sports', name: 'Sports / Athlete'},
  {code: 'public_private_institution', name: 'Public and private institution'},
  {code: 'other', name: 'Other public representative'},
];
