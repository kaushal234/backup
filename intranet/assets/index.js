// Javascript
//global.$ = global.jQuery = require('jquery')
const $ = require('jquery')
global.$ = global.jQuery = $
global.Cookies = require('js-cookie')
require('process');
require('bootstrap')
require('./bootstrap')

// Highcharts todo: a dedicated entry
global.Highcharts = require('highcharts')
require('highcharts/highcharts-more')(global.Highcharts)
require('highcharts/modules/exporting')(global.Highcharts)

require('./vendor/inspinia-admin-theme/js/inspinia.js')
require('./vendor/inspinia-admin-theme/js/plugins/footable/footable.all.min')
require('./javascript/foo_table')
require('./javascript/matrix_table')
require('./javascript/organization')
require('./javascript/status_menu')
require('./javascript/swiper')
require('bootstrap-datepicker')
require('@eonasdan/tempus-dominus')
require('./javascript/datepicker')
require('./javascript/highcharts')
require('./javascript/tooltip')
require('./javascript/tabs')
require('../src/ActivityBundle/Resources/public/js/activity')
require('./javascript/home')
require('./javascript/dual_select')
require('./javascript/quality/supplier_corrective_action_request')
require('./javascript/chat/chat')

// CSS

require('@fortawesome/fontawesome-free/css/all.min.css')
require('roboto-fontface/css/roboto/roboto-fontface.css')
require('open-sans-fontface/open-sans.css')
require('bootstrap-datepicker/dist/css/bootstrap-datepicker3.css')
// require('eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css')
require('@eonasdan/tempus-dominus')
require('react-widgets/styles.css')
//require('sweetalert/dist/sweetalert.css')
require('sweetalert2')
require('sweetalert2-react-content')
require('react-dropzone-component/styles/filepicker.css')
require('dropzone/dist/min/dropzone.min.css')
require('bootstrap-duallistbox/dist/bootstrap-duallistbox.min.css')
require('./stylesheets/app.scss')

require('./vendor/inspinia-admin-theme/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css')
if (module.hot) {
  module.hot.accept()
}
