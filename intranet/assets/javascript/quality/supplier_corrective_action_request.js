$(document).ready(function() {
    let toBeDisabled = document.getElementsByClassName('to-be-disabled')
    if (toBeDisabled.length > 0) {
        toBeDisabled[0].style.pointerEvents = "none";
        toBeDisabled[0].style.backgroundColor = "#F3F3F4";
    }
})
