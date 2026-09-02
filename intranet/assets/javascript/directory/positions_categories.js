$(document).ready(function() {
    $(document).on('click', '.dropdown-menu', function (e) {
        e.stopPropagation();
    });

    document.querySelector('#classification-edit-menu select').onchange = function () {
        document.querySelector('#classification-edit-menu').style.display = 'none'
        window.location = this.value
    };
});