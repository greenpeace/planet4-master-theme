const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const RemovePlugin = require('remove-files-webpack-plugin');
const dashDash = require('@greenpeace/dashdash');
const fs = require('fs');

const mediaQueryAliases = {
  '(max-width: 576px)': 'mobile-only',
  '(min-width: 576px)': 'small-and-up',
  '(min-width: 768px)': 'medium-and-up',
  '(min-width: 992px)': 'large-and-up',
  '(min-width: 1200px)': 'x-large-and-up',
};

const entryPoints = blockName => {
  const tpl = {
    [`${blockName}EditorScript`]: `./assets/src/blocks/${blockName}/${blockName}EditorScript.js`,
    [`${blockName}EditorStyle`]: `./assets/src/styles/blocks/${blockName}/${blockName}EditorStyle.scss`,
    [`${blockName}Script`]: `./assets/src/blocks/${blockName}/${blockName}Script.js`,
    [`${blockName}Style`]: `./assets/src/styles/blocks/${blockName}/${blockName}Style.scss`,
  };
  const pathExists = ([,path]) => fs.existsSync(path);
  return Object.fromEntries(Object.entries(tpl).filter(pathExists));
};

module.exports = {
  ...defaultConfig,
  entry: {
    editorIndex: './assets/src/editorIndex.js',
    frontendIndex: './assets/src/frontendIndex.js',
    carouselHeaderFrontIndex: './assets/src/carouselHeaderFrontIndex.js',
    style: './assets/src/styles/style.scss',
    editorStyle: './assets/src/styles/editorStyle.scss',
    lightbox: './assets/src/styles/lightbox.scss',
    themeEditor: './assets/src/themeEditorIndex.js',
    themeEditorStyle: './assets/src/styles/themeEditor.scss',
    theme_antarctic: './assets/src/styles/theme_antarctic.scss',
    theme_arctic: './assets/src/styles/theme_arctic.scss',
    theme_climate: './assets/src/styles/theme_climate.scss',
    theme_forest: './assets/src/styles/theme_forest.scss',
    theme_oceans: './assets/src/styles/theme_oceans.scss',
    theme_oil: './assets/src/styles/theme_oil.scss',
    theme_plastic: './assets/src/styles/theme_plastic.scss',
    ...entryPoints('Accordion'),
    ...entryPoints('Covers'),
    ...entryPoints('CarouselHeader'),
    ...entryPoints('Timeline'),
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
              sourceMap: true
            }
          },
          {
            loader: 'postcss-loader',
            options: {
              ident: 'postcss',
              plugins: () => [
                dashDash({mediaQueryAliases, mediaQueryAtStart: false}),
                require('autoprefixer'),
              ],
              sourceMap: true
            }
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true
            }
          }
        ]
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif)$/,
        use:
          [
            {
              loader: 'file-loader',
              options: {
                publicPath: __dirname + '/public'
              }
            }
          ]
      }
    ]
  },
  plugins: [
    ...defaultConfig.plugins,
    new FixStyleOnlyEntriesPlugin(),
    // extract css into dedicated file
    new MiniCssExtractPlugin({
      chunkFilename: '[id].css',
      ignoreOrder: false, // Enable to remove warnings about conflicting order
      filename: './[name].min.css'
    }),
    new RemovePlugin({
      after: {
        test: [
          {
            folder: 'assets/build/',
            method: (filePath) => {
              return [
                'editorStyle.deps.json',
                'style.deps.json',
                'theme_antarctic.deps.json',
                'theme_arctic.deps.json',
                'theme_climate.deps.json',
                'theme_forest.deps.json',
                'theme_oceans.deps.json',
                'theme_oil.deps.json',
                'theme_plastic.deps.json'
              ].filter(item => {
                return new RegExp(item, 'm').test(filePath);
              }).length > 0;
            }
          }
        ]
      }
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
