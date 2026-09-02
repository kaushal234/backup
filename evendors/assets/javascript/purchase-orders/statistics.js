function fetchPurchaseOrdersStatistics() {
    let statisticsElement = document.getElementById('purchase_orders_stats');

    if (statisticsElement) {
        let purchaseOrdersStatisticsAction = statisticsElement.getAttribute('data-action');

        fetch(purchaseOrdersStatisticsAction)
            .then(response => {
                if (!response.ok) {throw new Error('Network response was not ok');}
                return response.text();
            })
            .then(data => {statisticsElement.outerHTML = data})
            .catch(() => {statisticsElement.outerHTML = '<div class="alert alert-danger" role="alert">An error occurred while fetching data.</div>';});
    }
}

document.addEventListener('DOMContentLoaded', function () {
    fetchPurchaseOrdersStatistics();
});
