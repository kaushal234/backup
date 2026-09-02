import React from 'react'
import { render } from 'react-dom'
import App from './App'

global.$ = global.jQuery = require('jquery')

require('bootstrap3')
require('bootstrap3/dist/css/bootstrap.css')
require('font-awesome/css/font-awesome.css')
require('roboto-fontface/css/roboto-fontface.css')
require('open-sans-fontface/open-sans.css')
require('../stylesheets/app.scss')

render(<App/>, document.getElementById('app'))

if (module.hot) {
  module.hot.accept()
}
