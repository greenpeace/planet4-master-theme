const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const RemovePlugin = require('remove-files-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');

console.log('DIRECTORY: ', __dirname);

const icons_config = {
  shape: {
    dimension: {
      maxWidth: 64,
      maxHeight: 64
    },
    spacing: {
      padding: 0,
      box: 'content'
    }
  },
  mode: {
    inline: true,
    symbol: true
  }
};

module.exports = {
  ...defaultConfig,
  entry: {
    index: './assets/src/js/app.js',
    style: './assets/src/scss/style.scss',
    bootstrap: './assets/src/scss/bootstrap-build.scss',
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
          'css-loader',
          {
            loader: 'postcss-loader',
            options: {
              ident: 'postcss',
              plugins: function() {
                return require('autoprefixer');
              }
            }
          },
          'sass-loader'
        ]
      },
      {
        test: /icons\/.*\.svg$/,
        loader: 'svg-sprite-loader',
        options: {
          extract: true,
          spriteFilename: '/images/symbol/svg/symbol.svg',
          runtimeCompat: true
        }
      }
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
    })
  ],
  optimization: {
    ...defaultConfig.optimization,
    minimizer: [
      // enable the css minification plugin
      new TerserJSPlugin({}), new OptimizeCSSAssetsPlugin({})
    ]
  }
};
