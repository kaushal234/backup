export default {
    selector: '#request-for-quotation-lines-table',
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
