$('#block_tasks').on('ready', function(){
    $('.matrix-table').each(function () {
        var matrixReport = $(this);
        var allMatrixCells = matrixReport.find("td");
        matrixReport.find(allMatrixCells).on("mouseover", function () {
            var el = $(this), pos = el.index();
            el.parent().find("td").addClass("hover");
            allMatrixCells.filter(":nth-child(" + (pos + 1) + ")").addClass("hover");
        }).on("mouseout", function () {
            allMatrixCells.removeClass("hover");
        });
    });
});

var qsForm = $("#modules-quick-search form");
qsForm.submit(function() {
    var selectedModule = qsForm.find("#module").val();
    Cookies.set('tld-modules-quick-search', qsForm.find("#module").val());
});

$(document).ready(function() {
    module = Cookies.get('tld-modules-quick-search')

    if (null !== module) {
        qsForm.find("#module").val(module);
    }

    if ('seen' !== Cookies.get('tld-mis-modal')) {
        $('#mis-modal').modal('show');
        Cookies.set('tld-mis-modal', 'seen', { expires: 0.5 });
    }
});