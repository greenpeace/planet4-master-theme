# Greenpeace Planet 4 Gutenberg Blocks Plugin

![Planet 4](./planet4.png)

## What is it?

This the WordPress Gutenberg blocks plugin for Greenpeace Planet 4 project.
You can learn more about this project on [the Planet 4 blog](https://medium.com/planet4).

## Contribute

The best place to start is from the main [Planet 4 repo](https://github.com/greenpeace/planet4) that contains all the necessary information and tickets to get started.

## How to use this plugin in Planet 4

You can use the plugin in WordPress directly, by including it in your `composer.json` file:
```
"require": {
    ...
    "greenpeace/planet4-plugingutenberg-plugin" : "X.X.X",
    ...
},
```

## Assets build

You'll need npm to install the dependencies, just run  `npm install`  to install them.

To develop:

- run `npm start` to start a watcher that will rebuild every time you make a change.
- run `npm run build` to manually build the files.

## Build Setup

WordPress provides a single dependency for the whole build setup including:

* Babel: the transpiler for JSX & ES6 syntax to browser-compatible JS
* Webpack: the bundler for all the JS modules and dependency resolution

## How to develop a new block you ask?

1. Create a new class that extends `Base_Block` ( `P4GBKS\Blocks\Base_Block` ) inside directory _classes/blocks_. The class name should follow a naming convention, for example, **Blockname** and its file name should be class-**blockname**.php.

1. Implement its parent's class abstract method. In block's **constructor**, you need to define the block's details (fields, schema) using `register_block_type` and in method **prepare_data()** you need to prepare the data which will be used for rendering.

1. Create the template file that will be used to render your block inside directory _templates/blocks_. If the name of the file is **blockname**.twig then
you need to set the BLOCK_NAME constant as **'blockname'** It also works with HTML templates. Just add 'php' as the 3rd argument of the block() method.

1. Add your new class name to the array inside Loader's ( `P4GBKS\Loader` ) constructor.

1. Create a new folder inside _react-blocks/src/blocks_ named after your block **Blockname** (first letter capital - rest lowercase). Create two new files inside that folder named **Blockname.js**  and **BlocknameBlock.js**.

	 **BlocknameBlock.js** should be a class that uses wordpress [registerBlockType](https://developer.wordpress.org/block-editor/developers/block-api/block-registration/) to define the block's attributes, schema and `edit()` function.
 `edit()` function should return a react component that will be used for rendering the block in the editor.
`save()` function should return null as we use server-side rendering currently.

	**Blockname.js** should be a class that defines a React component that implements `renderEdit()` and `renderView()`.
`renderEdit()` should be used to render the block in the editor, to define editor-specific things as sidebar options, in-place edit components, and so on. `renderView()` will be used both in the editor and in the frontend site to render the block's contents, as we are rendering blocks using React in the frontend too.

	To learn more details about the rendering logic, refer to the [blocks page in Planet 4 Gitbook](https://support.greenpeace.org/planet4/tech/blocks).

1. Create a new sccs file inside _react-blocks/src/blocks/styles_ named after your block **Blockname.scss** to use for block's frontend styling.

    Create a new file named **BlocknameEditor.scss** to use for block's editor styling if you need to style the block in the editor.

1. Finally, before committing do **npm run build** to build the plugin's assets and **vendor/bin/phpcs** to check for any PHP styling errors in your code.
