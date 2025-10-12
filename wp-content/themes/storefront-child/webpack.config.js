const path = require('path');

module.exports = {
  entry: {
    'child-theme': './src/js/child-theme.js',
  },
  output: {
    path: path.resolve(__dirname, 'assets/js'),
    filename: '[name].js',
    clean: true,
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
  resolve: {
    extensions: ['.js'],
  },
  devtool: 'source-map',
  mode: 'development',
  watch: false,
};

