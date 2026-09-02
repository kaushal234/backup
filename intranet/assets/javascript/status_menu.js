$(document).on('click', '.status-menu', function (e) {
    e.stopPropagation();
});
document.querySelectorAll('.change-status-menu').forEach(function(item) {
    item.onchange = function () {
        this.style.display = 'none'
        window.location = this.options[this.selectedIndex].getAttribute('data-route')
    }
});
