const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const TerserJSPlugin = require('terser-webpack-plugin');
const SpriteLoaderPlugin = require('svg-sprite-loader/plugin');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const {BundleAnalyzerPlugin} = require('webpack-bundle-analyzer');
const {getWebpackEntryPoints} = require('@wordpress/scripts/utils');
const CopyWebpackPlugin = require('copy-webpack-plugin');

const srcDir = './assets/src/';
const getBlocksEntries = () => {
  process.env.WP_SRC_DIRECTORY = srcDir;
  return getWebpackEntryPoints();
};

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  const analyze = env && env.analyze;

  return {
    //stats: 'verbose',
    ...defaultConfig,
    mode: argv.mode || 'development',
    devtool: isProduction ? false : 'source-map',
    entry: {
      index: './assets/src/js/app.js',
      style: './assets/src/scss/style.scss',
      post: './assets/src/scss/post.scss',
      'resistance-hub-campaign-styles': './assets/src/scss/pages/_resistance-hub-campaign.scss',
      editorStyle: './assets/src/scss/editorStyle.scss',
      bootstrap: './assets/src/scss/bootstrap-build.scss',
      'country-selector': './assets/src/scss/partials/country-selector.scss',
      'gravity-forms': './assets/src/scss/layout/_gravity-forms.scss',
      'resistance-hub-campaign': './assets/src/js/resistance-hub-campaign.js',
      'gravityforms-client-side': './assets/src/js/gravityforms-client-side.js',
      media_archive: './assets/src/js/media_archive.js',
      media_archive_editor_view: './assets/src/js/media_archive_editor_view.js',
      'lite-yt-embed': './node_modules/lite-youtube-embed/src/lite-yt-embed.js',
      menu_editor: './assets/src/js/menu_editor.js',
      toggleCommentSubmit: './assets/src/js/toggle_comment_submit.js',
      turnstileRender: './assets/src/js/turnstile_render.js',
      hubspotCookie: './assets/src/js/hubspot_cookie.js',
      shareButtons: './assets/src/js/share_buttons.js',
      googleTagManager: './assets/src/js/google_tag_manager.js',
      vwoSmartCode: './assets/src/js/vwo_smart_code.js',
      hideWeakPasswordCheckbox: './assets/src/js/hide_weak_pw_checkbox.js', //NOSONAR
      listingPages: './assets/src/js/listing_pages.js',
      bulkExport: './assets/src/js/bulk_export.js',
      mediaImportButton: './assets/src/js/media_import_button.js',
      filterBlockNames: './assets/src/js/filter_block_names.js',
      dismissDashboardNotice: './assets/src/js/dismiss_dashboard_notice.js',
      frontendIndex: './assets/src/frontendIndex.js',
      editorIndex: './assets/src/editorIndex.js',
      GalleryScript: './assets/src/blocks/Gallery/GalleryScript.js',
      GalleryEditorScript: './assets/src/blocks/Gallery/GalleryEditorScript.js',
      GalleryStyle: './assets/src/scss/blocks/Gallery/GalleryStyle.scss',
      GalleryEditorStyle: './assets/src/scss/blocks/Gallery/GalleryEditorStyle.scss',
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
      TimelineScript: './assets/src/blocks/Timeline/TimelineScript.js',
      TimelineEditorScript: './assets/src/blocks/Timeline/TimelineEditorScript.js',
      TimelineStyle: './assets/src/scss/blocks/Timeline/TimelineStyle.scss',
      TimelineEditorStyle: './assets/src/scss/blocks/Timeline/TimelineEditorStyle.scss',
      ...getBlocksEntries(),
    },
    output: {
      filename: '[name].js',
      path: __dirname + '/assets/build',
    },
    module: {
      rules: [
        {
          test: /\.([jt])sx?$/,
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
        {
          test: /\.css$/i,
          use: ['style-loader', 'css-loader'],
        },
      ],
    },
    plugins: [
      ...defaultConfig.plugins,
      // build our blocks via block.json files
      new CopyWebpackPlugin({
        patterns: [
          {
            from: '**/block.json',
            context: srcDir,
          },
        ],
      }),
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
      ...(analyze ?
        [
          new BundleAnalyzerPlugin({
            analyzerMode: 'server',
            analyzerPort: 8888,
            openAnalyzer: true,
          }),
        ] :
        []),
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
  };
};
