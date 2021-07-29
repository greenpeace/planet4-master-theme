import { FrontendBlockNode } from '../components/FrontendBlockNode/FrontendBlockNode';

/**
 * This function is used in the `save()` method of `registerBlock` to
 * render React blocks in the frontend.
 *
 * It returns a function with the same arguments as the `save()`
 * method.
 *
 * Be careful! Making changes in this function or in the `FrontendBlockNode`
 * component could potentially cause block validation errors in Gutenberg.
 *
 * @param {string} block
 */
export const frontendRendered = ( block ) => {
  return ( attributes, className ) => {
    return <FrontendBlockNode
      attributes={ attributes }
      className={ className }
      blockName={ block }
    />;
  }
}

