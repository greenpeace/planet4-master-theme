# Greenpeace Planet4 Master Theme

![Planet4](./images/planet4.png)

## Introduction

### What is it?
This is the master Wordpress theme for Greenpeace Planet4 project.
You can learn more about this project on [the planet4 blog](https://medium.com/planet4).

This theme is based on the [`timber/starter-theme`](https://github.com/timber/starter-theme) codebase.
It has a correlated [child theme](https://github.com/greenpeace/greenpeace-planet4-child-theme).

## How to use this theme in planet4

You can use the theme in Wordpress directly, by including it at your `composer.json` file:
```
"require": {
    ...
    "greenpeace/planet4-master-theme" : "X.X.X",
    "greenpeace/planet4-child-theme" : "X.X.X",
    ...
},
```

By default the child theme is used but you can activate the master theme
by specifying it in `wp-cli.yml` like:
```
theme activate:
  - planet4-master-theme
```

You can then run `composer run-script theme:activate` to activate it.

## Task automation
We use gulp as automation tools for local development.

Available tasks

* `gulp sass` 'concatanates/compiles sass files into a minified single stylesheet'
* `gulp uglify` 'concatanates/mangles js files into a minified single js file'
* `gulp watch` 'watches for changes in js or sccs and runs the minification tasks'
* `gulp git_hooks` 'copies repo's git hooks to local git repo'

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

## Automated testing
Automated tests done using [BrowserStack](https://www.browserstack.com).

<img src="https://gist.githubusercontent.com/kirdia/2c7c68ed532310006bc4f5e50d6c06a2/raw/4d48287cd23a0d58694d1b9e61ffdf22cd7d8e66/browserstack-logo.svg?sanitize=true" alt="BrowserStack Logo" width="300" />
