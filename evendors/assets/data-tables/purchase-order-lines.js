import { populateDeliveryDate, initView, switchView, hideRecurrentInformation } from "./purchase-table/filter-view.js";
import { onChangeConfirmedDeliveryDate } from "./purchase-table/input-changed-date.js"

export default {
    selector: '#purchase-order-lines-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
            return {
                initComplete: function (settings, json) {
                    // no access to the modal if confirm_delivery_date is not modify
                    let accessModalButton = document.getElementById('access_confirm_delivery_date_modal');
                    accessModalButton.disabled = true;
                    let select = document.getElementById('confirmed_delivery_date_select_populateColumn');
                    select.disabled = false;
                    populateDeliveryDate(select, accessModalButton, 'purchase-order-lines-table');
                    onChangeConfirmedDeliveryDate(accessModalButton, 'purchase-order-lines-table');
                    hideRecurrentInformation('purchase-order-lines-table');
                    initView('purchase-order-lines-table');
                    switchView('purchase-order-lines-table');
                },
                columnDefs: [
                    {
                        targets: [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15],
                        orderable: false,
                    },
                ],
                paging: false,
                dom: 'frti',
            }
        }
};
