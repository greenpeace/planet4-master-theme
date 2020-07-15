const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const RemovePlugin = require('remove-files-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const dashDash = require('@greenpeace/dashdash');
const cssVariables = require( 'postcss-css-variables-extract' );
const fs = require( 'fs' );
const collectVarUsages = require( 'postcss-css-variables-extract/lib/scss-var-usages' );
const mergeVarUsages = require( 'postcss-css-variables-extract/lib/merge-var-usages' );

let allCssVars = {};
module.exports = {
  ...defaultConfig,
  entry: {
    index: './assets/src/js/app.js',
    style: './assets/src/scss/style.scss',
    bootstrap: './assets/src/scss/bootstrap-build.scss',
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
                dashDash(),
                 cssVariables( { preserve: true, exportVarUsagesTo: allCssVars } ),
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
          spriteFilename: '../../images/symbol/svg/sprite.symbol.svg',
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
    {
      apply: ( compiler ) => {
        compiler.hooks.done.tap( 'DoneWriteCssVars', ( ) => {
          // We use postcss to get the selector and resolved default value. For the original file and line number
          // we use a separate scripts which loops through all scss files. Only variables that are in the final css
          // are included.
          const scssUsages = collectVarUsages( './assets/src' );
          // console.log( 'CSS', allCssVars );
          // console.log( 'sass', scssUsages );
          const mergedUsages = mergeVarUsages( allCssVars, scssUsages );
          fs.writeFile(
            './assets/build/css-variables.json',
            JSON.stringify( mergedUsages, null, 2 ),
            console.log
          );
          allCssVars = {};
        } );
      }
    }
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
