function loadCharts() {
    $('.highcharts').each(function(index, element) {
        let options = $(element).data('options');

        let chart = Highcharts.chart(element, options);

        // Reload title because if empty chart, it will set default chart title
        if (options.title) {
            chart.setTitle(options.title);
        }
    });
}

document.addEventListener("DOMContentLoaded", function(event) {
    loadCharts();
});

/**
 * Reload charts of FCR when reload frame of datatable.
 */
if (document.getElementById('highcharts-fcr')) {
    document.addEventListener('turbo:before-fetch-response', async function (event) {
        // Get the new chart inside the new response.
        const newHighcharts = await event.detail.fetchResponse.responseHTML.then((value) => {
            const dom = new DOMParser().parseFromString(value, "text/html");
            return dom.getElementById('highcharts-fcr');
        });
        // Replace old charts.
        this.getElementById('highcharts-fcr').outerHTML = newHighcharts.outerHTML;
    });
}

// Reload highcharts components after re-render frame of datatable.
document.addEventListener('turbo:frame-load', function () {
    loadCharts();
});
