export default {
    selector: '#forecast-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
            return {
                paging: false,
                dom: 'Plfrti',
                buttons: [
                    'searchPanes'
                ],
                searchPanes: {
                    initCollapsed: true,
                    columns: [0, 1],
                    cascadePanes: true,
                }
            }
        }
};
