const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserJSPlugin = require('terser-webpack-plugin');

module.exports = {
  ...defaultConfig,
  entry: {
    editorIndex: './assets/src/editorIndex.js',
    frontendIndex: './assets/src/frontendIndex.js',
    style: './assets/src/styles/style.scss',
    editorStyle: './assets/src/styles/editorStyle.scss'
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
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'resolve-url-loader', 'sass-loader']
      },
      {
        test: /\.(png|svg|jpg|jpeg|gif)$/,
        use:
          ['url-loader']
      }
    ]
  },
  plugins: [
    ...defaultConfig.plugins,
    // extract css into dedicated file
    new MiniCssExtractPlugin({
      chunkFilename: '[id].css',
      ignoreOrder: false, // Enable to remove warnings about conflicting order
      filename: './[name].min.css'
    }),
  ],
  optimization: {
    ...defaultConfig.optimization,
    minimizer: [
      // enable the css minification plugin
      new TerserJSPlugin({}), new OptimizeCSSAssetsPlugin({})
    ]
  }
};
