const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const dashDash = require('@greenpeace/dashdash');
const webpack = require('webpack');

const mediaQueryAliases = {
  '(max-width: 576px)': 'mobile-only',
  '(min-width: 576px)': 'small-and-up',
  '(min-width: 768px)': 'medium-and-up',
  '(min-width: 992px)': 'large-and-up',
  '(min-width: 1200px)': 'x-large-and-up',
  '(min-width: 1600px)': 'xx-large-and-up',
};

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';

  return {
    //stats: 'verbose',
    mode: argv.mode || 'development',
    devtool: isProduction ? false : 'source-map',
    entry: {
      index: './assets/src/js/app.js',
      style: './assets/src/scss/style.scss',
      post: './assets/src/scss/post.scss',
      editorStyle: './assets/src/scss/editorStyle.scss',
      bootstrap: './assets/src/scss/bootstrap-build.scss',
      'country-selector': './assets/src/scss/partials/country-selector.scss',
      'gravity-forms': './assets/src/scss/layout/_gravity-forms.scss',
      'gravityforms-client-side': './assets/src/js/gravityforms-client-side.js',
      media_archive: './assets/src/js/media_archive.js',
      media_archive_editor_view: './assets/src/js/media_archive_editor_view.js',
      'lite-yt-embed': './node_modules/lite-youtube-embed/src/lite-yt-embed.js',
      menu_editor: './assets/src/js/menu_editor.js',
      frontendIndex: './assets/src/frontendIndex.js',
      editorIndex: './assets/src/editorIndex.js',
      GalleryScript: './assets/src/blocks/Gallery/GalleryScript.js',
      GalleryEditorScript: './assets/src/blocks/Gallery/GalleryEditorScript.js',
      GalleryStyle: './assets/src/scss/blocks/Gallery/GalleryStyle.scss',
      GalleryEditorStyle: './assets/src/scss/blocks/Gallery/GalleryEditorStyle.scss',
      GuestBookScript: './assets/src/blocks/GuestBook/GuestBookScript.js',
      GuestBookEditorScript: './assets/src/blocks/GuestBook/GuestBookEditorScript.js',
      CarouselHeaderScript: './assets/src/blocks/CarouselHeader/CarouselHeaderScript.js',
      CarouselHeaderEditorScript: './assets/src/blocks/CarouselHeader/CarouselHeaderEditorScript.js',
      AccordionScript: './assets/src/blocks/Accordion/AccordionScript.js',
      AccordionEditorScript: './assets/src/blocks/Accordion/AccordionEditorScript.js',
      CookiesScript: './assets/src/blocks/Cookies/CookiesScript.js',
      CookiesEditorScript: './assets/src/blocks/Cookies/CookiesEditorScript.js',
      CounterScript: './assets/src/blocks/Counter/CounterScript.js',
      CounterEditorScript: './assets/src/blocks/Counter/CounterEditorScript.js',
      SpreadsheetScript: './assets/src/blocks/Spreadsheet/SpreadsheetScript.js',
      SpreadsheetEditorScript: './assets/src/blocks/Spreadsheet/SpreadsheetEditorScript.js',
      SocialMediaEditorScript: './assets/src/blocks/SocialMedia/SocialMediaEditorScript.js',
      SocialMediaStyle: './assets/src/scss/blocks/SocialMedia/SocialMediaStyle.scss',
      SocialMediaEditorStyle: './assets/src/scss/blocks/SocialMedia/SocialMediaEditorStyle.scss',
      ENFormScript: './assets/src/blocks/ENForm/ENFormScript.js',
      ENFormEditorScript: './assets/src/blocks/ENForm/ENFormEditorScript.js',
      TimelineScript: './assets/src/blocks/Timeline/TimelineScript.js',
      TimelineEditorScript: './assets/src/blocks/Timeline/TimelineEditorScript.js',
      TimelineStyle: './assets/src/scss/blocks/Timeline/TimelineStyle.scss',
      TimelineEditorStyle: './assets/src/scss/blocks/Timeline/TimelineEditorStyle.scss',
    },
    output: {
      filename: '[name].js',
      path: __dirname + '/assets/build',
    },
    module: {
      rules: [
        {
          test: /\.(j|t)sx?$/,
          exclude: /node_modules/,
          use: [
            {
              loader: require.resolve('babel-loader'),
              options: {
                babelrc: false,
                configFile: false,
                presets: [
                  require.resolve('@wordpress/babel-preset-default'),
                ],
              },
            },
          ],
        },
        {
          test: /\.(sass|scss)$/,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                url: false,
                sourceMap: !isProduction,
              },
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: !isProduction,
                postcssOptions: {
                  ident: 'postcss',
                  plugins: [
                    dashDash({mediaQueryAliases, mediaQueryAtStart: false}),
                    require.resolve('autoprefixer'),
                  ],
                },
              },
            },
            {
              loader: 'sass-loader',
              options: {
                implementation: require.resolve('sass'),
                sourceMap: !isProduction,
              },
            },
          ],
        },
        {
          test: /icons\/.*\.svg$/,
          use: [{
            loader: 'svg-sprite-loader',
            options: {
              extract: true,
              spriteFilename: '../../assets/build/sprite.symbol.svg',
              runtimeCompat: true,
            },
          }],
        },
      ],
    },
    plugins: [
      // extract css into dedicated file
      new MiniCssExtractPlugin({
        chunkFilename: '[id].css',
        ignoreOrder: false, // Enable to remove warnings about conflicting order
        filename: './[name].min.css',
        runtime: false,
      }),
      new SpriteLoaderPlugin({
        plainSprite: true,
      }),
      // we should be able to remove this once we upgrade to WP 6.6,
      // because the new Gutenberg version will no longer use 'process'.
      new webpack.ProvidePlugin({
        process: 'process/browser.js',
      }),
    ],
    optimization: {
      concatenateModules: isProduction,
      minimizer: [
        // enable the css minification plugin
        new TerserJSPlugin({
          parallel: true,
        }),
        new CssMinimizerPlugin(),
      ],
    },
    stats: {
      children: false,
    },
  };
};
