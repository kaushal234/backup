module.exports = {
  plugins: {
    'postcss-import': {},
    'cssnext': {},
    'autoprefixer': {},
    'cssnano': {
      discardComments: { removeAll: true },
      reduceIdents: false,
      zindex: false
    }
  }
}
