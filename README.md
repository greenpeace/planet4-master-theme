# Greenpeace Planet 4 Styleguide

![Planet4](./theme/kss-assets/img/planet4.png)

## Run locally

Use [npm](https://www.npmjs.com/) to install the necessary [gulp-cli](https://gulpjs.com/):

    npm -g install gulp-cli

Install all required packages:

    npm install

Build the style guide:

    gulp build

Run the local webserver using [gulp](http://gulpjs.com/):

    gulp

Browse to [localhost:9000](http://localhost:9000).

## Contribute

### Building the styleguide with kss

The styleguide is generated from source sass files with [kss](http://kss-node.github.io/kss-node/).

In order to add new section to the styleguide you need to add a specific format of comment block to the appropriate partial file.

For instance adding this on top of [_buttsons.scss](./src/components/_buttons.scss):

```
// Buttons
//
// Markup:
// <div class="mb-2">
//   <a href="/international/act/protect-the-oceans/" class="btn {{modifier_class}}">Protect our seas</a>
// </div>
//
// .btn-primary - Primary button
// .btn-secondary - Secondary button
//
// Styleguide Components.buttons
```

Let's break this down:

1. Pick the title for that section.

```
// Buttons
```

2. Add the markup that you want to use as an example. It's important to add the word `Markup:` there.

```
// Markup:
// <div class="mb-2">
//   <a href="/international/act/protect-the-oceans/" class="btn {{modifier_class}}">Protect our seas</a>
// </div>
```

3. Optionally, you can have some variations. The classes defined here will substitute the `{{modifier_class}}` variable on the markup above.

```
// .btn-primary - Primary button
// .btn-secondary - Secondary button
```

4. Last thing on the comments block should be the hierarchy of the section, so that kss knows where to place it.

```
// Styleguide Components.buttons
```

### Styleguide theme

Our theme is based on the [michelangelo kss theme](https://github.com/stamkracht/michelangelo/), with some modification to meet the same look and feel we have on planet 4.

We can do changes on the theme by modifiying its sass code in the [theme/kss-assets/css](./theme/kss-assets/css) folder.

### Building

Every time we run `gulp build`, we generate both the styleguide theme, but also the unified css from our source files. The output folder is `dist`.

Running `gulp` we initiate the watch task and run a webserver at [localhost:9000](http://localhost:9000).
