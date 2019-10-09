# Greenpeace Planet4 Gutenberg Blocks Plugin  
  
![Planet4](./planet4.png)  
  
  
# Contents  
- [Introduction](#introduction)  
- [Build Setup](#build-setup)  
- [How to develop a new block you ask?](#how-to-develop-a-new-block-you-ask)
- [Assets build](#assets-build)  
- [Composer](#composer)  
- [Code Standards](#code-standards)  
- [Contribute](#contribute)  
  
## Introduction  
  
This WordPress plugin is an attempt to convert the old [shortcake shortcode blocks](https://github.com/greenpeace/planet4-plugin-blocks) to gutenberg blocks and implement new blocks in the future.

## Build Setup

Wordpress provides a single dependency for the whole build setup including:

* Babel: the transpiler for JSX & ES6 syntax to browser-compatible JS
* Webpack: the bundler for all the JS modules and dependency resolution

The default config expects a single entry point which produces a single output bundle and adds two directories to the root.

We use a customized config to have this:

```
react-blocks
  src                      - The JSX files
    blocks                   - Block definitions for the editor (title, icon)
    components               - Components used to render the blocks (reusable, shareable)
  build                    - The compiled output
  editorIndex.js          - The main JS for the editor
  frontendIndex.js        - The main JS for the frontend
```

### PHP Side

Everything is under `classes` folder, besided the plugin's entrypoint file (`planet4-gutenberg-blocks.php`)

```
classes
  blocks                   - Blocks classes
  controller               - Components used to render the blocks (reusable, shareable)
  class-loader.php         - Loader. Defines plugin's hooks/filters and blocks.
```


### Twig templates
Plugin depends on [timber-library](https://el.wordpress.org/plugins/timber-library/)  which is used for twig template engine. Twig templates are used to generate the blocks' output.
  
## How to develop a new block you ask?
  
1. Create a new class that extends `Base_Block` ( `P4GBKS\Blocks\Base_Block` ) inside directory _classes/blocks_. The class name should follow naming convention, for example **Blockname** and its file name should be class-**blockname**.php.  
  
1. Implement its parent's class abstract method. In block's **constructor** you need to define the block's details (fields, schema) using `register_block_type` and in method **prepare_data()** you need to prepare the data which will be used for rendering.  
  
1. Create the template file that will be used to render your block inside directory _templates/blocks_. If the name of the file is **blockname**.twig then  
you need to set the BLOCK_NAME constant as **'blockname'** It also works with html templates. Just add 'php' as the 3rd argument of the block() method.  
  
1. Add your new class name to the array inside Loader's ( `P4GBKS\Loader` ) constructor.
 
1. Create a new folder inside _react-blocks/src/blocks_ named after your block **Blockname** (first letter capital - rest lowercase). Create two new files inside that folder named **Blockname.js**  and **BlocknameBlock.js**.  
 
	 **BlocknameBlock.js** should be a class that uses wordpress [registerBlockType](https://developer.wordpress.org/block-editor/developers/block-api/block-registration/) to define the block's attributes, schema and `edit()` function.
 `edit()` function should return a react component that will be used for rendering the block in the editor. 
`save()` function should return null as we use server side rendering currently.

	**Blockname.js** should be a class that defines a react component that implements `renderEdit()` and `render()`.
`renderEdit()` should be used to render the block in the editor
`render()` should be used to render the block's preview in the editor using [ServerSideRender](https://developer.wordpress.org/block-editor/components/server-side-render/) and Preview components.

1. Create a new sccs file inside _react-blocks/src/blocks/styles_ named after your block **Blockname.scss** to use for block's frontend styling.

    Create a new file named **BlocknameEditor.scss** to use for block's editor styling if you need to style the block in the editor.
 
1. Finally, before committing do **npm run build** to build the plugin's assets and **vendor/bin/phpcs** to check for any php styling errors in your code.
  
  
## Assets build

You'll need NPM to install the dependencies, just run  `npm install`  to install them.

To develop:

-   run  `npm start`  to start a watcher on the  `react-blocks/src`  directory and rebuild everytime you make a change, output will be at  `react-blocks/build`.
    
-   run  `npm build`  to manually build the files.
  

## Composer  
We use composer as dependency manager for the this plugin.  
To install dependencies run  
  
`$ composer install`  
  
## Code standards  
We follow the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)  
  
We use a custom [php codesniffer](https://github.com/squizlabs/PHP_CodeSniffer) ruleset which adds some rules over WordPress-Core, WordPress-Docs and WordPress-Extra rulesets.  
  
[WordPress Coding Standards Rulesets](https://github.com/WordPress/WordPress-Coding-Standards)  
  
[WordPress Coding Standards Wiki](https://github.com/WordPress/WordPress-Coding-Standards/wiki)  
  
To run the [php codesniffer](https://github.com/squizlabs/PHP_CodeSniffer)  
  
`$ vendor/bin/phpcs`
 or   
`$ composer sniffs`  
  
To run the [php code beautifier and fixer](https://github.com/squizlabs/PHP_CodeSniffer/wiki/Fixing-Errors-Automatically)  
  
`$ vendor/bin/phpcbf`
 or   
`$ composer fixes`  
  
## Contribute  
  
Please read the [Contribution Guidelines](https://planet4.greenpeace.org/handbook/dev-contribute-to-planet4/) for Planet4.
