import { preFilter, unselectFilterBadge, addFilterBadge, filterAfterSubmit, saveFiltersOnSubmit } from "./purchase-table/search-functions.js";
import { onChangeConfirmedDeliveryDate } from "./purchase-table/input-changed-date.js"

export default {
    selector: '#purchase-orders-lines-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function(table) {
            let deliveryState = table.dataset.state;
            const COMPANY_COLUMN = 0;
            const LINE_STATUS_COLUMN = 13;
            const LINE_DELIVERY_STATUS_COLUMN = 14;
            const SUPPLIER_NUMBER_COLUMN = 15;

            const filterColumns = [COMPANY_COLUMN, LINE_STATUS_COLUMN, LINE_DELIVERY_STATUS_COLUMN, SUPPLIER_NUMBER_COLUMN];
            const hiddenColumns = [COMPANY_COLUMN, SUPPLIER_NUMBER_COLUMN];

            let config = {
                paging: false,
                dom: 'Plfrti',
                searchPanes: {
                    initCollapsed: true,
                    cascadePanes: true,
                    columns: filterColumns,
                    orthogonal: 'sao',
                    viewCountButton: false,
                    orderable: false,
                },
                columnDefs: [
                    ...hiddenColumns.map(columnIndex => ({ "visible": false, "targets": columnIndex })),
                    {
                        targets: LINE_DELIVERY_STATUS_COLUMN,
                        searchPanes: {
                            options: [
                                {
                                    label: '<span class="badge bg-warning">Late</span>',
                                    value: function(rowData, rowIdx) {
                                        return rowData[LINE_DELIVERY_STATUS_COLUMN].includes('Late');
                                    }
                                },
                                {
                                    label: '<span class="badge bg-info">To be delivered in 7 days</span>',
                                    value: function(rowData, rowIdx) {
                                        return rowData[LINE_DELIVERY_STATUS_COLUMN].includes('To be delivered in 7 days');
                                    }
                                },
                                {
                                    label: '<span class="badge bg-danger">Unconfirmed</span>',
                                    value: function(rowData, rowIdx) {
                                        return rowData[LINE_DELIVERY_STATUS_COLUMN].includes('Unconfirmed');
                                    }
                                },
                            ]
                        },
                    },
                ],
                initComplete: function (settings, json) {
                    $('#purchase-orders-lines-table')
                        .on('search.dt', function () {
                            addFilterBadge();
                            unselectFilterBadge();
                        })
                        .on( 'init.dt', function () {
                            preFilter(deliveryState);
                            filterAfterSubmit();
                            $('#purchase-orders-lines-table tbody').removeAttr('hidden');
                        } )
                    ;

                    // no access to the modal if confirm_delivery_date is not modify
                    let accessModalButton = document.getElementById('access_confirm_delivery_date_modal');
                    accessModalButton.disabled = true;

                    onChangeConfirmedDeliveryDate(accessModalButton, 'purchase-orders-lines-table');
                    saveFiltersOnSubmit();
                }
            }
            return config;
        }
};
