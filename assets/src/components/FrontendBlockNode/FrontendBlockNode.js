// This component is used in the `save()` method of `registerBlock`,
// via the `frontendRendered` function, to render React blocks in the frontend.

// Be careful! Making changes in this component or in the `frontendRendered`
// function could potentially cause block validation errors in Gutenberg.

export const FrontendBlockNode = props => {
  return (
    <div className={props.className}
      data-render={props.blockName}
      data-attributes={JSON.stringify(props.attributes)}>
    </div>
  );
};
