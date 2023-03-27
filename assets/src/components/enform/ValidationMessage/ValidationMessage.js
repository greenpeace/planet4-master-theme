export const ValidationMessage = props => {
  return (
    <div className="ValidationMessage">
      <ul>
        {props.message.map((validation_message, key) =>
          <li key={key}> {validation_message} </li>
        )}
      </ul>
    </div>
  );
};
