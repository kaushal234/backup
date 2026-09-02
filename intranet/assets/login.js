const $ = require('jquery')
global.$ = global.jQuery = $
global.Cookies = require('js-cookie')
require('process');
require('bootstrap')

require('./vendor/inspinia-admin-theme/js/inspinia.js')


require('@fortawesome/fontawesome-free/css/all.min.css')
require('roboto-fontface/css/roboto/roboto-fontface.css')
require('open-sans-fontface/open-sans.css')

require('./stylesheets/app.scss')
require('./vendor/inspinia-admin-theme/css/plugins/awesome-bootstrap-checkbox/awesome-bootstrap-checkbox.css')

$(document).ready(function() {
    passwordToggler = document.querySelector('.toggle-password');
    eyeIcon = document.querySelector('.toggle-password i');
    passwordInput = document.getElementById('password');

    passwordToggler.addEventListener("click", function (e) {
        e.preventDefault();
    });

    if ("ontouchstart" in document.documentElement) {
        passwordToggler.addEventListener("touchend", function (e) {
            passwordInput.type = passwordInput.type === 'text' ? 'password' : 'text';
            eyeIcon.classList.toggle('fa-eye-slash');
            eyeIcon.classList.toggle('fa-eye');
        });
    } else {
        passwordToggler.addEventListener("mousedown", function () {
            passwordInput.type = 'text';
            eyeIcon.classList.replace('fa-eye', 'fa-eye-slash');
        });
        passwordToggler.addEventListener("mouseup", function () {
            passwordInput.type = 'password';
            eyeIcon.classList.replace('fa-eye-slash', 'fa-eye');
        })
    }
});