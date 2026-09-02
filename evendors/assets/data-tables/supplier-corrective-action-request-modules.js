export default {
    selector: '#scar-modules-table',
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
