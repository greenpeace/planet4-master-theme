const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const RemovePlugin = require('remove-files-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const dashDash = require('@greenpeace/dashdash');

const mediaQueryAliases = {
  '(max-width: 576px)': 'mobile-only',
  '(min-width: 576px)': 'small-and-up',
  '(min-width: 768px)': 'medium-and-up',
  '(min-width: 992px)': 'large-and-up',
  '(min-width: 1200px)': 'x-large-and-up',
  '(min-width: 1600px)': 'xx-large-and-up',
};

module.exports = {
  ...defaultConfig,
  entry: {
    index: './assets/src/js/app.js',
    style: './assets/src/scss/style.scss',
    editorStyle: './assets/src/scss/editorStyle.scss',
    bootstrap: './assets/src/scss/bootstrap-build.scss',
    "country-selector-old": './assets/src/scss/partials/country-selector-old.scss',
    "country-selector-new": './assets/src/scss/partials/country-selector-new.scss',
    "navigation-bar-old": './assets/src/scss/layout/_navbar-old.scss',
    "navigation-bar-dark": './assets/src/scss/partials/navigation-bar-dark.scss',
    "navigation-bar-light": './assets/src/scss/partials/navigation-bar-light.scss',
    archive_picker: './assets/src/js/archive_picker.js',
    "lite-yt-embed": './node_modules/lite-youtube-embed/src/lite-yt-embed.js',
  },
  output: {
    filename: '[name].js',
    path: __dirname + '/assets/build'
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.(sass|scss)$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: {
              url: false,
              sourceMap: true,
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              ident: 'postcss',
              plugins: () => [
                dashDash({ mediaQueryAliases, mediaQueryAtStart: false }),
                require('autoprefixer'),
              ],
              sourceMap: true,
            }
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
            }
          }
        ]
      },
      {
        test: /icons\/.*\.svg$/,
        loader: 'svg-sprite-loader',
        options: {
          extract: true,
          spriteFilename: '../../assets/build/sprite.symbol.svg',
          runtimeCompat: true
        }
      },
    ]
  },
  plugins: [
    ...defaultConfig.plugins,
    // extract css into dedicated file
    new FixStyleOnlyEntriesPlugin(),
    new MiniCssExtractPlugin({
      chunkFilename: '[id].css',
      ignoreOrder: false, // Enable to remove warnings about conflicting order
      filename: './[name].min.css'
    }),
    new RemovePlugin({
      /**
       * After compilation removes all files in `dist/styles` folder,
       * that have `.map` type.
       */
      after: {
        test: [
          {
            folder: 'assets/build/',
            method: (filePath) => {
              return [
                'style.deps.json',
                'index.asset.php',
                'bootstrap.asset.php',
                'style.asset.php',
              ].filter(item => {
                return new RegExp(item, 'm').test(filePath);
              }).length > 0;
            }
          }
        ]
      }
    }),
    new SpriteLoaderPlugin({
      plainSprite: true
    }),
  ],
  optimization: {
    ...defaultConfig.optimization,
    minimizer: [
      // enable the css minification plugin
      new TerserJSPlugin({}),
      new OptimizeCSSAssetsPlugin({
        cssProcessorOptions: {
          sourceMap: true,
          map: {
            inline: false,
            annotation: true,
          }
        }
      })
    ]
  }
};
