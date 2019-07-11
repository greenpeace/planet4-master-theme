const defaultConfig = require("./node_modules/@wordpress/scripts/config/webpack.config");    // Require default Webpack config
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");

module.exports = {
  ...defaultConfig,
  entry: {
    editorIndex: './react-blocks/src/editorIndex.js',
    frontendIndex: './react-blocks/src/frontendIndex.js',
    blockStyles: './react-blocks/src/styles/style.scss'
  },
  output: {
    filename: '[name].js',
    path: __dirname + '/react-blocks/build'
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultConfig.module.rules,
      {
        test: /\.(sass|scss)$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader']
      }
    ]
  },
  plugins: [
    ...defaultConfig.plugins,
    // extract css into dedicated file
    new MiniCssExtractPlugin({
      filename: './style.min.css'
    })
  ],
  optimization: {
    ...defaultConfig.optimization,
    minimizer: [
      // enable the css minification plugin
      new OptimizeCSSAssetsPlugin({})
    ]
  }
};
