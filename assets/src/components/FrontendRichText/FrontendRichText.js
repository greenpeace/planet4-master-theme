import { Component, Fragment } from '@wordpress/element';

const RichText = wp.blockEditor ? wp.blockEditor.RichText : null;

export class FrontendRichText extends Component {
  constructor(props) {
    super(props);
	}

	render() {
		const { editable, ...richTextProps } = this.props;
		const renderAsRichText = RichText && editable;
		const TagName = richTextProps.tagName;

		return <Fragment>
			{
				renderAsRichText
				? <RichText { ...richTextProps } />
				: <TagName className={ richTextProps.className }>
						{ richTextProps.value }
					</TagName>
			}
		</Fragment>
	}
}
