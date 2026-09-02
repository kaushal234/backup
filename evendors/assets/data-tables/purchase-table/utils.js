import $ from 'jquery';

function getLines(tableId) {
    return document.querySelectorAll('table#'+ tableId +' > tbody > tr');
}

function getFormInLine(line) {
    return $(line).find('td div div input.confirmed-delivery-date-input');
}

function adaptColor(input) {
    let newValue = input.val();
    let initialValue = input.parents('td.confirm-delivery-date').attr('data-value');
    let parentNode = input.parent();

    resetConfirmedDeliveryDateInput(parentNode)
    if (initialValue !== newValue) {
        updateConfirmedDeliveryDateInput(parentNode)
    }
    resetDateButton();
}

function formatSupplierDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2)
        month = '0' + month;
    if (day.length < 2)
        day = '0' + day;

    return [year, month, day].join('-');
}

function resetConfirmedDeliveryDateInput(parentNode) {
    let input = $(parentNode).find('input')
    let saveButton = $(parentNode).find('button')

    input.css('color', '');
    input.css('border-color', '#6c757d');
    saveButton.css('color', '');
    saveButton.css('border', '');
    $(parentNode).find('.helper-date').remove();
    let errorMessage = $(parentNode).find('.invalid-feedback');
    if (errorMessage.length > 0) {
        $(errorMessage).remove();
        $(input).removeClass('is-invalid');
    }
}

function updateConfirmedDeliveryDateInput(parentNode) {
    let input = $(parentNode).find('input')
    let saveButton = $(parentNode).find('button')

    input.css('color', 'rgb(238, 162, 54)');
    input.css('border-color', 'rgb(238, 162, 54)');
    saveButton.css('color', '#ffc107');
    saveButton.css('border', 'solid');

    let resetDateMessage = $('#access_confirm_delivery_date_modal').attr('data-resetDateMessage');

    let resetDateNode = '<a role="button" class="resetDate">' + resetDateMessage + ' </a>';
    let helperNode = '<p class="helper-date" style="font: small italic; position: absolute; bottom: -20px; right: 0px">' + resetDateNode + '</p>';
    parentNode.append(helperNode);
}

function resetDateButton() {
    let resetDateButtons = $(document).find('td div div a.resetDate');
    resetDateButtons.each((i, item) => {
        $(item).on('click', function (e) {
            let parentNode = $(item).parents('td.confirm-delivery-date');
            let previousDate = new Date($(parentNode).attr('data-value'));
            let formatDate = isNaN(previousDate.getTime()) ? '' : formatSupplierDate(previousDate);
            let formInLine = $(parentNode).find('.confirmed-delivery-date-input');

            formInLine.val(formatDate.trim());
            adaptColor(formInLine);
        });

    });
}

export { getLines, getFormInLine, adaptColor, formatSupplierDate }