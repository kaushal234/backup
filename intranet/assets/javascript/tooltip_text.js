import { Tooltip } from 'bootstrap'

let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip-text"]'))
tooltipTriggerList.map(function (tooltipTriggerEl) {
    let tooltipText = tooltipTriggerEl.getAttribute('data-tooltip-text')

    return new Tooltip(tooltipTriggerEl, {
        delay: 100,
        placement: 'right',
        html: false,
        title: tooltipText
    })
})
