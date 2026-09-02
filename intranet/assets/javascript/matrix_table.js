$(document).ready(function() {

    $('.matrix-table:not(.matrix-table-no-hover)').each(function(){
        var matrixReport = $(this);
        var allMatrixCells = matrixReport.find("td");
        matrixReport.find(allMatrixCells)
            .on("mouseover", function() {
                var el = $(this),
                    pos = el.index();
                el.parent().find("td").addClass("hover");
                allMatrixCells.filter(":nth-child(" + (pos+1) + ")").addClass("hover");
            })
            .on("mouseout", function() {
                allMatrixCells.removeClass("hover");
            });
    });

});
