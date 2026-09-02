import { getLines, getFormInLine, formatSupplierDate, adaptColor } from "./utils.js";
import $ from 'jquery';

let uselessSummaryInformation = ["confirmedSupplierDate", "price", "status", "lineTexts"];
let recurrentInformation = ["lineIdentifier", "partNumber", "description", "revision", "expired", "supplierItemCode"];

function populateDeliveryDate(select, accessModalButton, tableId) {
    select.onchange = function () {
        let lines = getLines(tableId);
        let selectedDate = select.value;
        let deniedAccessModal = true;

        lines.forEach((item, i) => {
            let formInLine = getFormInLine(item);
            if (formInLine.length > 0) {
                let date = new Date(item.querySelector('[data-name="' + selectedDate + '"]').getAttribute('data-value'));
                let formatDate = isNaN(date.getTime()) ? '' : formatSupplierDate(date);

                formInLine.val(formatDate.trim());
                deniedAccessModal = false;
                adaptColor(formInLine)
            }

        });
        accessModalButton.disabled = deniedAccessModal;
    };
}

function initView(tableId) {
    viewOpenLine(tableId);

    if (document.getElementById('flexRadioAll').checked === true) {
        viewAllLines(tableId);
    }
}

function switchView(tableId) {

    const radioButtons = document.querySelectorAll('input[name="flexRadioSwitchView"]');
    for (const radioButton of radioButtons) {
        radioButton.addEventListener('change', function (e) {
            if (document.getElementById('flexRadioAll').checked === true) {
                viewAllLines(tableId);
            }
            if (document.getElementById('flexRadioOpenLine').checked === true) {
                viewOpenLine(tableId);
            }
        });
    }
}

function viewAllLines(tableId) {
    $(document).find('table#'+ tableId +' tr').show();
    hideRecurrentInformation(tableId);
}

function hideRecurrentInformation(tableId) {
    let lines = getAllLines(tableId);

    lines.each((i, line) => {
        let lineIdentifier = getLineIdentifierFromLine(line);
        if (getLinesWithRemainder(tableId).includes(lineIdentifier) && !isOrderSummary(line)) {
            recurrentInformation.forEach((item, i) => {
                hideInformation(line, item);
            })
        }
        if (isOrderSummary(line)) {
            uselessSummaryInformation.forEach((item, i) => {
                hideInformation(line, item);
            })
        }
    })
}

function viewOpenLine(tableId) {
    let lines = getAllLines(tableId);
    lines.hide();
    lines.each((i, line) => {
        recurrentInformation.forEach((item, i) => {
            showInformation(line, item);
        })
        if ($(line).find('td div[data-is-confirmable="1"]').length > 0) {
            $(line).show();
        }
    })
}

function hideInformation(line, item) {
    $(line).find('td div[data-name="' + item + '"]').hide();
}

function showInformation(line, item) {
    $(line).find('td div[data-name="' + item + '"]').show();
}

function getLinesWithRemainder(tableId) {
    let lines = getAllLines(tableId);
    let lineWithRemainder = [];
    lines.each((i, line) => {
        if (isOrderSummary(line)) {
            lineWithRemainder.push(getLineIdentifierFromLine(line));
        }
    })
    return lineWithRemainder;
}


function isOrderSummary(line) {
    return $(line).find('td div[data-sequence="0"]').length > 0;
}

function getLineIdentifierFromLine(line) {
    return $.trim($(line).find('td div[data-name="lineIdentifier"]').text());
}

function getAllLines(tableId) {
    return $(document).find('table#'+ tableId +' > tbody > tr');
}

export { populateDeliveryDate, initView, switchView, hideRecurrentInformation }