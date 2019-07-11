# Greenpeace Planet4 Gutenberg Blocks Plugin

![Planet4](./planet4.png)


# Contents
- [Introduction](#introduction)
- [Override default twig templates](#overide-default-twig-templates-of-blocks)
- [Task automation](#task-automation)
- [Composer](#composer)
- [Code Standards](#code-standards)
- [Contribute](#contribute)

## Introduction

This WordPress plugin provides the necessary blocks to be used with Shortcake UI plugin.

## Overide default twig templates of blocks

You can overide the default block twig templates by including in your child theme a file with the same name in the subdirectory
`/templates/plugins/planet4-plugin-gutenberg-blocks/includes/`


**How to develop a new block you ask?**

1. Create a new controller class that extends Controller inside directory _classes/controller/blocks_. The class name should follow naming convention, for example **Blockname**_Controller and its file name should be class-**blockname**-controller.php.

2. Implement its parent's class two abstract methods. In method **prepare_fields()** you need to define the blocks fields and in method **prepare_data()** you need to prepare the data which will be used for rendering.

3. Create the template file that will be used to render your block inside directory _includes/blocks_. If the name of the file is **blockname**.twig then
you need to set the BLOCK_NAME constant as **'blockname'** It also works with html templates. Just add 'php' as the 3rd argument of the block() method.

4. Add your new class name to the array that the Loader function takes as an argument in the plugin's main file.

5. Finally, before committing do **composer update --no-dev** and **composer dump-autoload --optimize** in order to add your new class to composer's autoload.


## Task automation
We use gulp as automation tools for local development.

Available tasks

* `gulp lint_css` 'checks for css/sass lint errors'
* `gulp lint_js` 'checks for js lint errors'
* `gulp sass` 'concatanates/compiles sass files into a minified single stylesheet'
* `gulp sass_admin` 'concatanates/compiles admin sass files into a minified single stylesheet'
* `gulp uglify` 'concatanates/mangles js files into a minified single js file'
* `gulp uglify_admin` 'concatanates/mangles admin js files into a minified single js file'
* `gulp watch` 'watches for changes in js or sccs and runs the minification tasks'
* `gulp git_hooks` 'copies repo's git hooks to local git repo'

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
