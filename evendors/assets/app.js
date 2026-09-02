import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-datepicker/dist/css/bootstrap-datepicker3.css';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';
import 'datatables.net-select-bs5/css/select.bootstrap5.min.css';
import 'datatables.net-searchpanes-bs5/css/searchPanes.bootstrap5.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './styles/app.css';

import $ from 'jquery';
window.$ = window.jQuery = $;

import 'bootstrap';
import 'bootstrap-datepicker';
import 'datatables.net-searchpanes-bs5';
import 'datatables.net-select-bs5';

import Highcharts from 'highcharts';
import HighchartsMore from 'highcharts/highcharts-more';
import Exporting from 'highcharts/modules/exporting';
HighchartsMore(Highcharts);
Exporting(Highcharts);
window.Highcharts = Highcharts;

import DataTables from './data-tables/index.js';

$(document).ready(() => {
    $('.js-datepicker').datepicker({
        format: 'yyyy-mm-dd'
    })
    $('.js-delivery-date-datepicker').datepicker({
        format: 'yyyy-mm-dd',
        startDate: 'now',
        endDate: '2100-09-30'
    });

    $('#locale-switcher').on('change', (event) => {
        let select = event.target
        let current = select.dataset.current
        let value = select.value
        if (value !== current) {
            let data = new FormData()
            data.append('locale', value)

            fetch(select.dataset.action, {method: 'POST', body: data})
                .then(() => {
                    document.location.reload()
                })
        }
    })

    DataTables.forEach((dataTable) => {
        const table = document.querySelector(dataTable.selector);
        if (table) {
            $(table).DataTable(dataTable.settings(table));
        }
    });

    $('.highcharts').each(function(index, element) {
        let options = $(element).data('options');

        let chart = Highcharts.chart(element, options);

        if (options.title) {
            chart.setTitle(options.title);
        }
    });

    $('#supplier_ranking_chart_select').on('change', function(event) {
        $('.highcharts').hide();
        $('#supplier_ranking_chart_' + event.target.value).show();
    })
})
