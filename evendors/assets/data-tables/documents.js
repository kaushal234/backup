export default {
    selector: '#documents-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
            return {
                paging: false,
                dom: 'fti',
            };
        }
};
