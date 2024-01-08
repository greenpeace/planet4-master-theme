
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const dashDash = require('@greenpeace/dashdash');

const isProduction = process.env.NODE_ENV === 'production';
const mode = isProduction ? 'production' : 'development';

const mediaQueryAliases = {
  '(max-width: 576px)': 'mobile-only',
  '(min-width: 576px)': 'small-and-up',
  '(min-width: 768px)': 'medium-and-up',
  '(min-width: 992px)': 'large-and-up',
  '(min-width: 1200px)': 'x-large-and-up',
  '(min-width: 1600px)': 'xx-large-and-up',
};

module.exports = {
  mode,
  devtool: isProduction ? false : 'source-map',
  entry: {
    index: './assets/src/js/app.js',
    style: './assets/src/scss/style.scss',
    post: './assets/src/scss/post.scss',
    editorStyle: './assets/src/scss/editorStyle.scss',
    bootstrap: './assets/src/scss/bootstrap-build.scss',
    "country-selector": './assets/src/scss/partials/country-selector.scss',
    "gravity-forms": './assets/src/scss/layout/_gravity-forms.scss',
    "gravityforms-client-side": './assets/src/js/gravityforms-client-side.js',
    media_archive: './assets/src/js/media_archive.js',
    media_archive_editor_view: './assets/src/js/media_archive_editor_view.js',
    "lite-yt-embed": './node_modules/lite-youtube-embed/src/lite-yt-embed.js',
    menu_editor: './assets/src/js/menu_editor.js',
    frontendIndex: './assets/src/blocks/frontendIndex.js',
    editorIndex: './assets/src/blocks/editorIndex.js',
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
  },
  output: {
    filename: '[name].js',
    path: __dirname + '/assets/build'
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
              cacheDirectory: process.env.BABEL_CACHE_DIRECTORY || true,
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
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              sourceMap: !isProduction,
              postcssOptions: {
                ident: 'postcss',
                plugins: [
                  dashDash({ mediaQueryAliases, mediaQueryAtStart: false }),
                  require.resolve('autoprefixer'),
                ],
              },
            },
          },
          {
            loader: 'sass-loader',
            options: {
              implementation: require.resolve("sass"),
              sourceMap: !isProduction,
            }
          }
        ]
      },
      {
        test: /icons\/.*\.svg$/,
        use: [{
          loader: 'svg-sprite-loader',
          options: {
            extract: true,
            spriteFilename: '../../assets/build/sprite.symbol.svg',
            runtimeCompat: true
          }
        }],
      },
    ]
  },
  plugins: [
    // extract css into dedicated file
    new MiniCssExtractPlugin({
      chunkFilename: '[id].css',
      ignoreOrder: false, // Enable to remove warnings about conflicting order
      filename: './[name].min.css',
    }),
    new SpriteLoaderPlugin({
      plainSprite: true
    }),
  ],
  optimization: {
    concatenateModules: isProduction,
    minimizer: [
      // enable the css minification plugin
      new TerserJSPlugin( {
        parallel: true,
      }),
      new CssMinimizerPlugin(),
    ]
  }
};
