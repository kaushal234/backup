import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import '@fontsource/barlow-condensed/400.css';
import '@fontsource/barlow-condensed/500.css';
import '@fontsource/barlow-condensed/600.css';
import '@fontsource/barlow-condensed/700.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/jetbrains-mono/400.css';
import '@fontsource/jetbrains-mono/500.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import { Tooltip } from 'bootstrap';
import 'bootstrap';
import './styles/partial/table.css';
import './styles/partial/top_message.css';
import './styles/app.css';
import './styles/extranet-theme.css';
import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap-datepicker';
import './styles/partial/datepicker.css';

$(document).ready(() => {
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd'
    })

    document.addEventListener('turbo:frame-render', function () {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd'
        })
    });

    const initTooltips = () => {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new Tooltip(el));
    };
    document.addEventListener('DOMContentLoaded', initTooltips);
    document.addEventListener('turbo:load', initTooltips);
    document.addEventListener('turbo:frame-render', initTooltips);
})
