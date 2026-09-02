import {Tooltip, Popover} from 'bootstrap'

function loadTooltip() {
    let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip-image"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        let imgSrcElement = '<img src="'+tooltipTriggerEl+'" width="180px" />'
        return new Tooltip(tooltipTriggerEl, {
            delay: 100,
            placement: 'right',
            html: true,
            title: imgSrcElement,
        })
    });

    [...document.querySelectorAll('[data-bs-toggle="tooltip"]')].map(tooltipTriggerEl => {
        return new Tooltip(tooltipTriggerEl);
    });

    [...document.querySelectorAll('[data-bs-toggle="popover"]')].map(popoverTriggerEl => {
        return new Popover(popoverTriggerEl, {
            container: 'body'
        });
    });
}

function clearTooltip() {
    [...document.querySelectorAll('.tooltip')].map((element) => {
        element.remove();
    });
    [...document.querySelectorAll('.popover')].map((element) => {
        element.remove();
    });
}

document.addEventListener("DOMContentLoaded", function () {
    loadTooltip();
});

document.addEventListener('turbo:frame-render', function () {
    clearTooltip();
    loadTooltip();
});

document.addEventListener('turbo:click', function () {
    // Remove all tooltips when click on a link inside the turbo frame.
    clearTooltip();
});