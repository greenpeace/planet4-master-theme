const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');
const FixStyleOnlyEntriesPlugin = require("webpack-fix-style-only-entries");
const dashDash = require('@greenpeace/dashdash');

const mediaQueryAliases = {
  '(max-width: 576px)': 'mobile-only',
  '(min-width: 576px)': 'small-and-up',
  '(min-width: 768px)': 'medium-and-up',
  '(min-width: 992px)': 'large-and-up',
  '(min-width: 1200px)': 'x-large-and-up',
};

const jsConfig = {
  ...defaultConfig,
  output: {
    filename: '[name].js',
    path: __dirname + '/assets/build'
  },
  optimization: {
    minimizer: [
      new TerserJSPlugin({}),
    ]
  },
};

const publicJsConfig = {
  ...jsConfig,
  resolve: {
    alias: {
      '@hooks': 'preact/hooks',
      '@render': 'preact',
      '@compat': 'preact/compat',
    }
  },
  entry: {
    frontendIndex: './assets/src/frontendIndex.js',
    AccordionScript: './assets/src/blocks/Accordion/AccordionScript.js',
    CoversScript: './assets/src/blocks/Covers/CoversScript.js',
    CarouselHeaderScript: './assets/src/blocks/CarouselHeader/CarouselHeaderScript.js',
    ENFormScript: './assets/src/blocks/ENForm/ENFormScript.js',
    SpreadsheetScript: './assets/src/blocks/Spreadsheet/SpreadsheetScript.js',
    TimelineScript: './assets/src/blocks/Timeline/TimelineScript.js',
    GalleryScript: './assets/src/blocks/Gallery/GalleryScript.js',
    HubspotFormScript: './assets/src/blocks/HubspotForm/HubspotFormScript.js',
  },
};

const adminJsConfig = {
  ...jsConfig,
  resolve: {
    alias: {
      '@hooks': '@wordpress/element',
      '@render': '@wordpress/element',
      '@compat': '@wordpress/element',
    }
  },
  entry: {
    editorIndex: './assets/src/editorIndex.js',
    themeEditor: './assets/src/themeEditorIndex.js',
    AccordionEditorScript: './assets/src/blocks/Accordion/AccordionEditorScript.js',
    CoversEditorScript: './assets/src/blocks/Covers/CoversEditorScript.js',
    CarouselHeaderEditorScript: './assets/src/blocks/CarouselHeader/CarouselHeaderEditorScript.js',
    ENFormEditorScript: './assets/src/blocks/ENForm/ENFormEditorScript.js',
    SpreadsheetEditorScript: './assets/src/blocks/Spreadsheet/SpreadsheetEditorScript.js',
    TimelineEditorScript: './assets/src/blocks/Timeline/TimelineEditorScript.js',
    SocialMediaEditorScript: './assets/src/blocks/SocialMedia/SocialMediaEditorScript.js',
    GalleryEditorScript: './assets/src/blocks/Gallery/GalleryEditorScript.js',
    HubspotFormEditorScript: './assets/src/blocks/HubspotForm/HubspotFormEditorScript.js',
  },
};
const cssConfig = {
  ...defaultConfig,
  entry: {
    style: './assets/src/styles/style.scss',
    editorStyle: './assets/src/styles/editorStyle.scss',
    lightbox: './assets/src/styles/lightbox.scss',
    themeEditorStyle: './assets/src/styles/themeEditor.scss',
    AccordionStyle: './assets/src/styles/blocks/Accordion/AccordionStyle.scss',
    AccordionEditorStyle: './assets/src/styles/blocks/Accordion/AccordionEditorStyle.scss',
    CarouselHeaderStyle: './assets/src/styles/blocks/CarouselHeader/CarouselHeaderStyle.scss',
    CarouselHeaderEditorStyle: './assets/src/styles/blocks/CarouselHeader/CarouselHeaderEditorStyle.scss',
    ENFormStyle: './assets/src/styles/blocks/ENForm/ENFormStyle.scss',
    ENFormEditorStyle: './assets/src/styles/blocks/ENForm/ENFormEditorStyle.scss',
    SpreadsheetStyle: './assets/src/styles/blocks/Spreadsheet/SpreadsheetStyle.scss',
    TimelineStyle: './assets/src/styles/blocks/Timeline/TimelineStyle.scss',
    TimelineEditorStyle: './assets/src/styles/blocks/Timeline/TimelineEditorStyle.scss',
    SocialMediaStyle: './assets/src/styles/blocks/SocialMedia/SocialMediaStyle.scss',
    SocialMediaEditorStyle: './assets/src/styles/blocks/SocialMedia/SocialMediaEditorStyle.scss',
    CoversStyle: './assets/src/styles/blocks/Covers/CoversStyle.scss',
    GalleryStyle: './assets/src/styles/blocks/Gallery/GalleryStyle.scss',
    GalleryEditorStyle: './assets/src/styles/blocks/Gallery/GalleryEditorStyle.scss',
    HubspotFormStyle: './assets/src/styles/blocks/HubspotForm/HubspotFormStyles.scss',
    HubspotFormEditorStyle: './assets/src/styles/blocks/HubspotForm/HubspotFormEditorStyles.scss',
  },
  output: {
    filename: '[name].js',
    path: __dirname + '/assets/build'
  },
  module: {
    rules: [
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
  ],
  optimization: {
    minimizer: [
      // enable the css minification plugin
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
  },
};
module.exports = [publicJsConfig, adminJsConfig, cssConfig];
