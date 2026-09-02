import { preFilter, unselectFilterBadge, addFilterBadge } from "./purchase-table/search-functions.js";
import $ from 'jquery';

export default {
    selector: '#purchase-orders-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function(table) {
            let deliveryState = table.dataset.state;
            let config = {
                paging: false,
                dom: 'Plfrti',
                searchPanes: {
                    initCollapsed: true,
                    cascadePanes: true,
                    columns: [1, 2, 7, 8],
                    orthogonal: 'sao',
                    viewCountButton: false,
                    orderable: false,
                },
                columnDefs: [
                    {
                        targets: 8,
                        searchPanes: {
                            options: [
                                {
                                    label: '<span class="badge bg-warning">Late</span>',
                                    value: function(rowData, rowIdx) {
                                        return rowData[8].includes('Late');
                                    }
                                },
                                {
                                    label: '<span class="badge bg-info">To be delivered in 7 days</span>',
                                    value: function(rowData, rowIdx) {
                                        return rowData[8].includes('To be delivered in 7 days');
                                    }
                                },
                            ]
                        },
                    },
                ],
                initComplete: function (settings, json) {
                    $('#purchase-orders-table')
                        .on('search.dt', function () {
                            addFilterBadge();
                            unselectFilterBadge();
                        })
                        .on( 'init.dt', function () {
                            preFilter(deliveryState);
                            $('#purchase-orders-table tbody').removeAttr('hidden');
                        } )
                    ;
                }
            }
            return config;
        }
};