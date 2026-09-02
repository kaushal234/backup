$('.monthpicker').on('change', function(event) {
    event.target.value = formatStringDate(event.target);
});

function formatStringDate(target) {
    let input = target.value;
    let parts = input.split("/");
    let month = parts[0];
    let year = parts[1];
    let day;

    if (target.name.toLowerCase().includes('from') || target.name.toLowerCase().includes('start')) {
        day = getFirstDayOfMonth(year, month);
    }

    if (target.name.toLowerCase().includes('to') || target.name.toLowerCase().includes('end')) {
        day = getLastDayOfMonth(year, month);
    }

    return month + '/' + day + '/' + year;
}

function getFirstDayOfMonth(year, month) {
    let day = new Date(year, month, 1).getDate().toString();
    if (day.length === 1 ) {
        day = '0' + day;
    }
    return day;
}

function getLastDayOfMonth(year, month) {
    return new Date(year, month, 0).getDate().toString();
}
