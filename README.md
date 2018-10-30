# Greenpeace Planet4 Master Theme

![Planet4](./planet4.png)

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

## Contribute

Please read the [Contribution Guidelines](https://planet4.greenpeace.org/handbook/dev-contribute-to-planet4/) for Planet4.
