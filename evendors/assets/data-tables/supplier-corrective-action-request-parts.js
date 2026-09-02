export default {
    selector: '#scar-parts-table',
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
