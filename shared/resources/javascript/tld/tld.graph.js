
$(function() {

	// Default configuration ----->

    Highcharts.setOptions({
        credits: {
            enabled: false,
            text: 'tld-group.com',
            href: 'http://www.tld-group.com'
        }
    });

    // Create graph ----------->

    $('.tldGraph').each(function(){

        // Get data
        var parameters = $(this).data('tldgraph');
        // Apply style
        $(this).css({
            "min-width": (parameters.chart && parameters.chart.width || 310) + "px",
            "height": (parameters.chart && parameters.chart.height || 400) + "px",
            "margin": "10px auto"
        });

        // Make it as a graph
        $(this).highcharts(parameters);
    });

});
