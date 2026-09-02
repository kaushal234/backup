export default {
    selector: '#scar-table',
    settings:
        /**
         * @param {HTMLTableElement} table
         */
        function (table) {
            return {
                pageLength: 50,
                dom: "ftr" +
                    "<'row'<'ps-2 pt-2 col-sm-12 col-md-5'i><'d-flex justify-content-end col-sm-12 col-md-7'p>>",
            };
        }
};
