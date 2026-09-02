export default {
    selector: '#purchase-order-lines-label-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
            return {
                paging: false,
                dom: 'frti',
            }
        }
};