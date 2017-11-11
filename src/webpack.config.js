var path = require('path');
// var ExtractTextPlugin = require('extract-text-webpack-plugin');

var webpackConfig = {
  resolve: {
    root: path.join(__dirname, 'node_modules'),
    alias: {
      components: '../../components' // 组件别名,js里引用路径可直接 'components/xxx/yyy'
    },
    extensions: ['', '.js', '.vue', '.scss', '.css', 'less']
  },
  output: {
    // publicPath: 'yourcdnlink/static/',
    path: path.resolve(__dirname, './static_new'),
    filename: 'js/[name].js',
    // chunkFilename: 'js/[id].js?[hash]'
  },
  module: {
    noParse: [/vue.js/],
    preloaders: [
      {test: /\.(vue|js)$/, loader: 'eslint', exclude: /node_modules/}
    ],
    loaders: [
      { test: /\.vue$/, loader: 'vue' },
      { test: /\.js$/, loader: 'babel', exclude: /node_modules/ },
      {
        test: /\.(png|jpe?g|gif)(\?.*)?$/,
        loader: 'url',
        query: {
          limit: 5000, // 换成你想要得大小
          name: 'images/[name].[ext]?[hash:10]'
        }
      },
      {
        test: /\.(woff2?|eot|ttf|otf|svg)(\?.*)?$/,
        loader: 'url',
        query: {
          limit: 5000, // 换成你想要得大小
          name: 'fonts/[name].[hash:7].[ext]'
        }
      }
    ]
  },

  devtool: 'source-map',
  plugins: [
    // new ExtractTextPlugin("css/[name].[hash].css", { allChunks: true }),
  ],
  babel: { //配置babel
    "presets": ["es2015", 'stage-2'],
    "plugins": ["transform-runtime"]
  }
};

module.exports = webpackConfig
