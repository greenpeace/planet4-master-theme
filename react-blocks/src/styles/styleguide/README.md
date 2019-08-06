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

## Deployment

The build and deployment is happening automatically whenever:

- There are commits to the master branch.
- There are new [tags](https://github.com/greenpeace/planet4-styleguide/tags) created.

The above changes trigger (via the circleCI API) a rebuild of the develop and master related workflows of the current repository.

These in turn do the following:

- Checkout the relevant code of the styleguide (either the latest code of the master theme, or the latest tag).
- Create a docker image with the above code in the repository called "public".
- Push this docker image to the [docker hub registry](https://hub.docker.com/r/greenpeaceinternational/p4-styleguide) for the current application and tag it either `develop` or `latest`.
- Run a helm deploy/update to create the necessary kubernetes resources so that this can be served by our kubernetes clusters.

### Notes

- This repository does not have its own helm chart. It utilises the helm chart [Planet4 static](https://github.com/greenpeace/planet4-helm-static) which can been created to accomodate all static applications.
- New commits to the master branch get deployed at the url: https://develop.planet4.greenpeace.org/styleguide/
- New tags get deployed at the url: https://planet4.greenpeace.org/styleguide/
- At the bottom left corner of those implementations you can see the hash (or the tag number) of the code used to build it.
