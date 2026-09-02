export default {
    selector: '#vendor-warranty-claim-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
             return {
                pageLength: 50,
                dom: "Pftr" +
                    "<'row'<'ps-2 pt-2 col-sm-12 col-md-5'i><'d-flex justify-content-end col-sm-12 col-md-7'p>>",
                buttons: [
                    'searchPanes',
                ],
                searchPanes: {
                    initCollapsed: true,
                    columns: [1, 2, 4],
                    orthogonal: 'sao',
                    cascadePanes: true,
                },
            };
        }
};
