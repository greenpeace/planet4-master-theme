import {CountrySelector} from './CountrySelector';
import {PositionSelector} from './PositionSelector';
import {inputId} from './inputId';

const {__} = wp.i18n;

export const FormGenerator = ({fields, attributes, onInputChange, onBlur, errors}) => {
  const {en_form_style} = attributes;
  const is_side_style = 'side-style' === en_form_style;

  const control_fields = {};
  for (const f of fields) {
    if (f.dependency) {
      // eslint-disable-next-line no-unused-expressions
      control_fields[f.dependency] ?
        control_fields[f.dependency].push(f.name) :
        control_fields[f.dependency] = [f.name];
    }
  }

  return (
    <div className="formblock-flex donations-formsection-info">
      {fields.map((field, index) => {
        return (
          <Input
            key={`field-${field.id || index}`}
            {...{field, index, onInputChange, onBlur, control_fields, errors, is_side_style}}
          />
        );
      })}
    </div>
  );
};

const Input = props => {
  const {
    field,
    index,
    onInputChange = () => {
      // no action by default
    },
    onBlur = () => {
      // no action by default
    },
    control_fields,
    errors,
    is_side_style,
  } = props;

  return (returnField => {
    switch (returnField?.input_type) {
    case 'text':
    case 'email':
      return <TextInput {...{field: returnField, onInputChange, onBlur, errors, is_side_style}} />;
    case 'checkbox':
      return <CheckboxInput {...{field: returnField, onInputChange, onBlur, index, control_fields, errors, is_side_style}} />;
    case 'radio':
      return <RadioInput {...{field: returnField, onInputChange, onBlur, errors, is_side_style}} />;
    case 'country':
      return <CountryInput {...{field: returnField, onInputChange, onBlur, errors, is_side_style}} />;
    case 'position':
      return <PositionInput {...{field: returnField, onInputChange, onBlur, errors, is_side_style}} />;
    case 'hidden':
      return <HiddenInput {...{field: returnField}} />;
    default:
      throw `Input type <${returnField?.input_type}> unknown.`;
    }
  })(field);
};

const HiddenInput = ({field}) => {
  const {name} = inputId(field);
  return (
    <input
      type="hidden"
      name={name}
      value={field.default_value}
      readOnly={true} />
  );
};

const TextInput = ({field, onInputChange, onBlur, errors, is_side_style}) => {
  const {id, name} = inputId(field);
  const has_error = errors && errors[field.id];
  const errorMessage = field.input_type === 'email' ?
    __('Please enter a valid e-mail address.', 'planet4-engagingnetworks') :
    __('This field is required', 'planet4-engagingnetworks');

  const label = `${field.label}${field.required ? ' *' : ''}`;

  return (
    <div
      className={`en__field en__field--text en__field--${field.id} en__field--${field.property}`}
    >
      <div
        className="en__field__element en__field__element--text form-group animated-label"
        style={field.en_type === 'GEN' ? {display: 'flex', flexDirection: 'row'} : {display: 'block'}}
      >
        <input
          id={id}
          name={name}
          type={'phoneNumber' === field.name ? 'tel' : field.input_type}
          className={`en__field__input en__field__input--text form-control ${has_error ? 'is-invalid' : ''}`}
          defaultValue={field.default_value}
          data-errormessage={errorMessage}
          data-validate_regex={field.js_validate_regex}
          data-validate_regex_msg={field.js_validate_regex_msg}
          data-validate_callback={field.js_validate_function}
          placeholder={label}
          required={field.required || field.input_type === 'email'}
          size="40"
          onChange={e => onInputChange(field, e)}
          onBlur={e => onBlur(field, e)}
        />
        {is_side_style &&
          <label
            className="en__field__top__label"
            htmlFor={`en__field_supporter_questions_${field.id}`}
          >
            {label}
          </label>
        }
        {has_error &&
          <div className="invalid-feedback">{ errors[field.id] ?? errorMessage }</div>
        }
      </div>
    </div>
  );
};

const CheckboxInput = ({field, onInputChange, onBlur, index, control_fields, errors}) => {
  return field.en_type === 'GEN' ?
    <CheckboxGen {...{field, onInputChange, onBlur, index, control_fields, errors}} /> :
    <CheckboxOpt {...{field, onInputChange, onBlur, control_fields, errors}} />;
};

const CheckboxOpt = ({field, onInputChange, onBlur, control_fields, errors}) => {
  const {id, name} = inputId(field);
  const has_error = errors && errors[field.id];
  const errorMessage = __('This field is required', 'planet4-engagingnetworks');

  return (
    <div className={`en__field en__field--check en__field--${field.id}`}>
      <div
        className="en__field__element en__field__element--check form-group form-check-label-block custom-control p4-custom-control-input"
      >
        <label className={`custom-checkbox ${field.dependency ? 'disable-checkbox' : ''}`} htmlFor={id}>
          <input
            id={id}
            name={name}
            type="checkbox"
            className={`en__field__input en__field__input--checkbox ${field.dependency ? 'dependency-' + field.name : ''} ${has_error ? 'is-invalid' : ''}`}
            defaultValue={field.default_value}
            data-errormessage={errorMessage}
            defaultChecked={1 === field.selected}
            required={field.required}
            disabled={field.dependency ? true : false}
            data-dependency={control_fields[field.name] ? control_fields[field.name].join() : null}
            onClick={toggleDependencies}
            onChange={e => onInputChange(field, e)}
            onBlur={e => onBlur(field, e)}
          />
          <span
            className="custom-control-description"
            dangerouslySetInnerHTML={{__html: `${field.label}${field.required ? ' *' : ''}`}}
          />
          {has_error &&
          <div className="invalid-feedback">{ errors[field.id] ?? errorMessage }</div>
          }
        </label>
      </div>
    </div>
  );
};

const CheckboxGen = ({field, onInputChange, onBlur, control_fields, errors}) => {
  const {id, name} = inputId(field);
  const question_option = {};
  const has_error = errors && errors[field.id];
  const errorMessage = __('This field is required', 'planet4-engagingnetworks');

  return (
    <div className={`en__field en__field--check en__field--${field.id}`}>
      <div className="en__field__element en__field__element--check form-group form-check-label-block custom-control p4-custom-control-input">
        <label className="custom-checkbox" htmlFor={id}>
          <input
            id={id}
            name={name}
            type="checkbox"
            className={`en__field__input en__field__input--checkbox ${field.dependency ? 'dependency-' + field.dependency : ''} ${has_error ? 'is-invalid' : ''}`}
            defaultValue={question_option.option_value}
            data-errormessage={errorMessage}
            defaultChecked={question_option.option_selected}
            required={field.required}
            disabled={field.dependency ? true : false}
            data-dependency={control_fields[field.name] ? control_fields[field.name].join() : null}
            onClick={toggleDependencies}
            onChange={e => onInputChange(field, e)}
            onBlur={e => onBlur(field, e)}
          />
          <span
            className="custom-control-description"
            dangerouslySetInnerHTML={{__html: `${question_option.option_label}${field.required ? ' *' : ''}`}}
          />
          {errors && errors.includes(field.id) &&
            <div className="invalid-feedback">{errors[field.id] ?? errorMessage}</div>
          }
          <br />
        </label>
      </div>
    </div>
  );
};

const RadioInput = ({field, onInputChange, onBlur, errors}) => {
  const {id, name} = inputId(field);
  const options = field.radio_options[field.locale] || [];
  const has_error = errors && errors[field.id];

  const inputs = options.map((opt, index) => {
    return (
      <div key={index} className={`en__field en__field--check en__field--${field.id}`}>
        <div className="en__field__element en__field__element--check form-group form-check-label-block custom-control p4-custom-control-input">
          <label className="custom-radio" htmlFor={id}>
            <input
              id={id}
              name={name}
              type="radio"
              className="en__field__input en__field__input--radio"
              value={opt.option_value}
              data-errormessage={__('This field is required', 'planet4-engagingnetworks')}
              checked={opt.option_selected}
              required={field.required}
              onChange={e => onInputChange(field, e)}
              onBlur={e => onBlur(field, e)}
            />
            <span className="custom-control-description">
              { opt.option_label }
            </span><br />
          </label>
        </div>
      </div>
    );
  });

  if (inputs.length <= 0) {
    return null;
  }

  const errorMessage = __('This field is required', 'planet4-engagingnetworks');

  return (
    <div className="en__field">
      <span className="custom-control-description">
        {field.label}
      </span><br />
      { inputs }
      {has_error &&
        <div className="invalid-feedback">{errors[field.id] ?? errorMessage}</div>
      }
    </div>
  );
};

const CountryInput = ({field, onInputChange, onBlur, errors}) => {
  const {id, name} = inputId(field);
  const has_error = errors && errors[field.id];
  const error_message = __('Please select a country.', 'planet4-engagingnetworks');
  const props = {
    id,
    name,
    class_name: `en__field__input en__field__input--select en_select_country form-select ${has_error ? 'is-invalid' : ''}`,
    default_text: `${__('Select Country or Region', 'planet4-engagingnetworks')}${field.required ? ' *' : ''}`,
    error_message,
    required: field?.required || false,
    label: `${field.label}${field.required ? ' *' : ''}`,
    onInputChange: e => onInputChange(field, e),
    onBlur: e => onBlur(field, e),
  };

  return (
    <div
      className={`en__field en__field--${field.id} en__field--${field.property} en__field--select`}
    >
      <div className="en__field__element en__field__element--select form-group animated-label">
        <CountrySelector {...props} />
        {has_error &&
          <div className="invalid-feedback">{ errors[field.id] ?? error_message }</div>
        }
      </div>
    </div>
  );
};

const PositionInput = ({field, onInputChange, onBlur, errors, is_side_style}) => {
  const {id, name} = inputId(field);
  const has_error = errors && errors[field.id];
  const error_message = __('Please select a position.', 'planet4-engagingnetworks');
  const props = {
    id,
    name,
    class_name: `en__field__input en__field__input--select en_select_position form-select ${has_error ? 'is-invalid' : ''}`,
    default_text: `${__('Select Affiliation, Position or Profession', 'planet4-engagingnetworks')}${field.required ? ' *' : ''}`,
    error_message,
    required: field?.required || false,
    onInputChange: e => onInputChange(field, e),
    onBlur: e => onBlur(field, e),
  };

  return (
    <div
      className={`en__field en__field--${field.id} en__field--${field.property} en__field--select`}
    >
      <div className="en__field__element en__field__element--select form-group animated-label">
        {is_side_style &&
          <label className="en__field__top__label" htmlFor={name}>
            {field.label}{field.required ? ' *' : ''}
          </label>
        }
        <PositionSelector {...props} />
        {has_error &&
          <div className="invalid-feedback">{ errors[field.id] ?? error_message }</div>
        }
      </div>
    </div>
  );
};

/**
 * Toggles availability of checkboxes depending on the one clicked
 *
 * @param {Object} e
 */
const toggleDependencies = e => {
  const target = e.target;
  const dependencies = target?.dataset?.dependency;
  if (!target || !dependencies) {
    return;
  }

  for (const dependency of dependencies.split(',')) {
    const dep_element = document.querySelector(`.dependency-${dependency}`);
    if (!dep_element) {
      continue;
    }

    if (target.checked) {
      dep_element.removeAttribute('disabled');
      // eslint-disable-next-line no-unused-expressions
      dep_element.parentElement?.classList.remove('disable-checkbox');
    } else {
      dep_element.setAttribute('disabled', '');
      dep_element.checked = false;
      // eslint-disable-next-line no-unused-expressions
      dep_element.parentElement?.classList.add('disable-checkbox');
    }
  }
};
