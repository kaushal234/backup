import {adaptColor, getFormInLine, getLines} from './utils.js';
import $ from 'jquery';

function onChangeConfirmedDeliveryDate(accessModalButton, tableId) {
    let lines = getLines(tableId);

    lines.forEach((item, i) => {
        let formInLine = getFormInLine(item);
        if (formInLine.length > 0) {
            $(formInLine).on('change', function (e) {
                adaptColor($(this))
                accessModalButton.disabled = false;
            });
        }
    });
}

export { onChangeConfirmedDeliveryDate }