import {
  getAllBlocks,
  selectBlockByClientId,
  pressKeyWithModifier,
} from '@wordpress/e2e-test-utils';

/**
 * Selects a block by name.
 *
 * @param {string} name The block name.
 */
export const selectBlockByName = async ( name ) => {
  const selectedBlock = ( await getAllBlocks() ).find( ( block ) => block.name === name );
  if ( !selectedBlock ) {
    throw `Block not found: [${name}]`;
  }

  await selectBlockByClientId(
    selectedBlock.clientId
  );
};

/**
 * This helper is specific to the Color Picker component.
 * It selects a Color by name, using the aria-label property.
 *
 * @param {string} name Color name.
 */
export const selectColorByName = async ( name ) => {
  const [ element ] = await page.$x( `//div[contains(@class,"edit-post-sidebar")]//button[contains(@aria-label,"${ name }")]` );
  await element.click();
};

/**
 * Clicks an element containing certain text.
 *
 * @param {string} elementExpression An XPath expression to locate the element.
 * @param {string} text The text to match.
 */
export const clickElementByText = async ( elementExpression, text ) => {
  const [ element ] = await page.$x( `//${ elementExpression }[contains(text(),"${ text }")]` );
  await element.click();
};

/**
 * Types in an input element based on its label.
 *
 * @param {string} label Text of the label before the text input.
 * @param {string} value Value to be applied to the input.
 */
export const typeInInputWithLabel = async ( label, value ) => {
  const [ inputEl ] = await page.$x( `//label[@class="components-base-control__label"][contains(text(),"${ label }")]/following-sibling::input[@class="components-text-control__input"]` );
  const propertyHandle = await inputEl.getProperty('id');
  const inputId = await propertyHandle.jsonValue();
  await page.type( `#${ inputId }`, value);
};

/**
 * Opens a Sidebar panel containing certain title.
 *
 * @param {string} title The sidebar panel title.
 */
export const openSidebarPanelWithTitle = async ( title ) => {
  // Check if the sidebar panel exists.
  await page.waitForXPath( `//div[contains(@class,"edit-post-sidebar")]//button[@class="components-button components-panel__body-toggle"][contains(text(),"${ title }")]` );

  // Only open panel if it's not expanded already (aria-expanded check).
  const [ panel ] = await page.$x(
    `//div[contains(@class,"edit-post-sidebar")]//button[@class="components-button components-panel__body-toggle"][@aria-expanded="false"][contains(text(),"${ title }")]`
  );

  if ( panel ) {
    await panel.click();
  }
};

/**
 * This helper is specific to the select block style.
 * It selects a block style by name, using the aria-label property.
 *
 * @param {string} name Style name.
 */
export const selectStyleByName = async ( name ) => {
  let [ element ] = await page.$x( `//div[contains(@class,"edit-post-sidebar")]//div[contains(@aria-label,"${ name }")]` );

  if ( ! element ) {
    [ element ] = await page.$x( `//div[@class="block-editor-block-styles"]//span[contains(text(),"${ name }")]` );
  }

  await element.click();
};

/**
 * Types in an inline input element(richtext) based on its placeholder label.
 *
 * @param {string} label Placeholder text of the rich text input.
 * @param {string} value Value to be applied to the input.
 */
export const typeInInputWithPlaceholderLabel = async ( label, value ) => {
  const [ element ] = await page.$x( `//*[contains(@class,"block-editor-rich-text__editable")][contains(@aria-label,"${ label }")]` );
  await element.click();
  await page.waitForSelector( ':focus.rich-text' );
  await page.keyboard.type( value );
};

/**
 * Types in an textarea element based on its label.
 *
 * @param {string} label Text of the label before the text input.
 * @param {string} value Value to be applied to the input.
 */
export const typeInTextareaWithLabel = async ( label, value ) => {
  const [ inputEl ] = await page.$x( `//label[@class="components-base-control__label"][contains(text(),"${ label }")]/following-sibling::textarea[@class="components-textarea-control__input"]` );
  const propertyHandle = await inputEl.getProperty('id');
  const inputId = await propertyHandle.jsonValue();

  await page.type( `#${ inputId }`, value);
};

/**
 * Types in an inline checkbox input element(richtext) based on its name.
 *
 * @param {string} name Name of checkbox input.
 * @param {string} value Value to be applied to the input.
 */
export const typeInInputWithCheckbox = async ( name, value ) => {
  const [ element ] = await page.$x( `//*[contains(@class,"p4-custom-control-input")][contains(@name,"${ name }")]` );
  await element.click();
  await page.keyboard.type( value );
};

/**
 * Types in an autocomplete input element based on its label.
 *
 * @param {string} label Label text of the text input.
 * @param {string} value Value to be applied to the input.
 */
export const typeInDropdownWithLabel = async ( label, value ) => {
  // Wait for 0.5 sec.
  await new Promise((r) => setTimeout(r, 500));
  const [ inputEl ] = await page.$x( `//label[contains(text(),"${ label }")]/following-sibling::div//input[@class="components-form-token-field__input"]` );
  if ( inputEl ) {
    const propertyHandle = await inputEl.getProperty('id');
    const inputId = await propertyHandle.jsonValue();
    await page.type( `#${ inputId }`, value.slice(0, 4));
    await page.$eval('span[aria-label="'+value+'"]', (el) => el.click() );
  }
};

/**
 * Remove the existing text of input field, if exists.
 *
 * @param {string} label Text of the label before the text input.
 */
export const clearPreviousTextWithLabel = async ( label ) => {
  const [ inputEl ] = await page.$x( `//label[@class="components-base-control__label"][contains(text(),"${ label }")]/following-sibling::input[@class="components-text-control__input"]` );

  const valuePropertyHandle = await inputEl.getProperty('value');
  const inputValue = await valuePropertyHandle.jsonValue();
  if ( inputValue ) {
    await inputEl.click();
    await pressKeyWithModifier( 'primary', 'a' );
    await page.keyboard.press( 'Backspace' );
  }
};

/**
 * Remove the existing text of richtext field, if exists.
 *
 * @param {string} placeholder Text of the richtext input.
 */
export const clearPreviousTextWithPlaceholder = async ( placeholder ) => {
  const [ inputEl ] = await page.$x( `//*[contains(@class,"block-editor-rich-text__editable")][contains(@aria-label,"${ placeholder }")]` );
  await inputEl.click();
  let inputValue = await page.evaluate(
    () => document.activeElement.textContent
  );

  if ( inputValue ) {
    await pressKeyWithModifier( 'primary', 'a' );
    await page.keyboard.press( 'Backspace' );
  }
};
