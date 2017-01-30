# Greenpeace Planet 4 Master Theme

This is the Planet 4 project’s Wordpress global theme. 
It’s based on the [`timber/starter-theme`](https://github.com/timber/starter-theme) codebase and customized to our needs.

_Note: This has a correlated Wordpress sample child theme in https://github.com/greenpeace/greenpeace-planet4-child-theme._

![Logo banner for this repository “Greenpeace Planet 4 Master Theme”](./screenshot.png)

## How to use the Theme in Wordpress

You can use the theme in Wordpress directly. Follow these steps:

1. Add the scoped repository name into your Wordpress’ `composer.json`: `anselmh/greenpeace-global-theme` in the section `dependencies`. 2. Run `composer update` 
4. Test it on your local setup
5. If everything is fine, commit your changes and push it

## Development

### Setup

For development, we require a couple of tools.

- [yarn](https://yarnpkg.com/) as npm client, front-end dependency manager
- [Composer](https://getcomposer.org/) as PHP dependency manager
- [Twig](http://twig.sensiolabs.org/)
- [Timber](https://timber.github.io/timber/)
- [PostCSS](http://postcss.org/) for automated CSS transformation
