export default {
    selector: '#request-for-quotation-table',
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
                    columns: [1, 3, 5],
                    orthogonal: 'sao',
                    cascadePanes: true,
                },
                columnDefs: [
                    {
                        targets: 5,
                        render: function (data, type, row) {
                            if (type === 'sao') {
                                let values = [];
                                let temp = document.createElement('div');
                                temp.innerHTML = data;
                                for (let i = 0; i < temp.children.length; i++) {
                                    values.push(temp.children.item(i).textContent);
                                }

                                return values;
                            }

                            return data;
                        },
                        searchPanes: {
                            orthogonal: 'sao'
                        }
                    }
                ]
            }
        }
};
