/**
 * This file is copy of core button block edit.js (https://github.com/WordPress/gutenberg/blob/373affc62f64889c1092bc89f6ba59a4fdfcdfb9/packages/block-library/src/button/edit.js), with customize changes.
 * Customize changes(PLANET-4924) :
 *  - Added `p4_button_text_colors` and `p4_button_bg_colors` custom P4 button colors.
 *  - Remove the BorderPanel control(button borderRadius).
 *  - Remove the WidthPanel control(button width).
 */

/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useCallback, useState } from '@wordpress/element';
import {
	KeyboardShortcuts,
	PanelBody,
	TextControl,
	ToggleControl,
	ToolbarButton,
	ToolbarGroup,
	Popover,
} from '@wordpress/components';
import {
	BlockControls,
	InspectorControls,
	RichText,
	useBlockProps,
	__experimentalLinkControl as LinkControl,
	__experimentalUseEditorFeature as useEditorFeature,
} from '@wordpress/block-editor';
import { rawShortcut, displayShortcut } from '@wordpress/keycodes';
import { createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import ColorEdit from './color-edit';
import getColorAndStyleProps from './color-props';

const NEW_TAB_REL = 'noreferrer noopener';

const EMPTY_ARRAY = [];

function URLPicker( {
	isSelected,
	url,
	setAttributes,
	opensInNewTab,
	onToggleOpenInNewTab,
	anchorRef,
} ) {
	const [ isURLPickerOpen, setIsURLPickerOpen ] = useState( false );
	const urlIsSet = !! url;
	const urlIsSetandSelected = urlIsSet && isSelected;
	const openLinkControl = () => {
		setIsURLPickerOpen( true );
		return false; // prevents default behaviour for event
	};
	const unlinkButton = () => {
		setAttributes( {
			url: undefined,
			linkTarget: undefined,
			rel: undefined,
		} );
		setIsURLPickerOpen( false );
	};
	const linkControl = ( isURLPickerOpen || urlIsSetandSelected ) && (
		<Popover
			position="bottom center"
			onClose={ () => setIsURLPickerOpen( false ) }
			anchorRef={ anchorRef?.current }
		>
			<LinkControl
				className="wp-block-navigation-link__inline-link-input"
				value={ { url, opensInNewTab } }
				onChange={ ( {
					url: newURL = '',
					opensInNewTab: newOpensInNewTab,
				} ) => {
					setAttributes( { url: newURL } );

					if ( opensInNewTab !== newOpensInNewTab ) {
						onToggleOpenInNewTab( newOpensInNewTab );
					}
				} }
			/>
		</Popover>
	);
	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					{ ! urlIsSet && (
						<ToolbarButton
							name="link"
							icon="admin-links"
							title={ __( 'Link' ) }
							shortcut={ displayShortcut.primary( 'k' ) }
							onClick={ openLinkControl }
						/>
					) }
					{ urlIsSetandSelected && (
						<ToolbarButton
							name="link"
							icon="editor-unlink"
							title={ __( 'Unlink' ) }
							shortcut={ displayShortcut.primaryShift( 'k' ) }
							onClick={ unlinkButton }
							isActive={ true }
						/>
					) }
				</ToolbarGroup>
			</BlockControls>
			{ isSelected && (
				<KeyboardShortcuts
					bindGlobal
					shortcuts={ {
						[ rawShortcut.primary( 'k' ) ]: openLinkControl,
						[ rawShortcut.primaryShift( 'k' ) ]: unlinkButton,
					} }
				/>
			) }
			{ linkControl }
		</>
	);
}

function ButtonEdit( props ) {
	const {
		attributes,
		setAttributes,
		className,
		isSelected,
		onReplace,
		mergeBlocks,
	} = props;
	const {
		borderRadius,
		linkTarget,
		placeholder,
		rel,
		text,
		url,
		width,
	} = attributes;
	const onSetLinkRel = useCallback(
		( value ) => {
			setAttributes( { rel: value } );
		},
		[ setAttributes ]
	);
	const colors = useEditorFeature( 'color.palette' ) || EMPTY_ARRAY;

	const onToggleOpenInNewTab = useCallback(
		( value ) => {
			const newLinkTarget = value ? '_blank' : undefined;

			let updatedRel = rel;
			if ( newLinkTarget && ! rel ) {
				updatedRel = NEW_TAB_REL;
			} else if ( ! newLinkTarget && rel === NEW_TAB_REL ) {
				updatedRel = undefined;
			}

			setAttributes( {
				linkTarget: newLinkTarget,
				rel: updatedRel,
			} );
		},
		[ rel, setAttributes ]
	);

	const colorProps = getColorAndStyleProps( attributes, colors, true );
	const blockProps = useBlockProps();

	return (
		<>
			<ColorEdit { ...props } />
			<div
				{ ...blockProps }
				className={ classnames( blockProps.className, {
					[ `has-custom-width wp-block-button__width-${ width }` ]: width,
				} ) }
			>
				<RichText
					aria-label={ __( 'Button text' ) }
					placeholder={ placeholder || __( 'Add text…' ) }
					value={ text }
					onChange={ ( value ) => setAttributes( { text: value } ) }
					withoutInteractiveFormatting
					className={ classnames(
						className,
						'wp-block-button__link',
						colorProps.className,
						{
							'no-border-radius': borderRadius === 0,
						}
					) }
					style={ {
						borderRadius: borderRadius
							? borderRadius + 'px'
							: undefined,
						...colorProps.style,
					} }
					onSplit={ ( value ) =>
						createBlock( 'core/button', {
							...attributes,
							text: value,
						} )
					}
					onReplace={ onReplace }
					onMerge={ mergeBlocks }
					identifier="text"
				/>
			</div>
			<URLPicker
				url={ url }
				setAttributes={ setAttributes }
				isSelected={ isSelected }
				opensInNewTab={ linkTarget === '_blank' }
				onToggleOpenInNewTab={ onToggleOpenInNewTab }
				anchorRef={ blockProps.ref }
			/>
			<InspectorControls>
				<PanelBody title={ __( 'Link settings' ) }>
					<ToggleControl
						label={ __( 'Open in new tab' ) }
						onChange={ onToggleOpenInNewTab }
						checked={ linkTarget === '_blank' }
					/>
					<TextControl
						label={ __( 'Link rel' ) }
						value={ rel || '' }
						onChange={ onSetLinkRel }
					/>
				</PanelBody>
			</InspectorControls>
		</>
	);
}

export default ButtonEdit;
