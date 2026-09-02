import $ from 'jquery';

function preFilter(deliveryState){
    // table init with In Progress Filter active
    $('.dtsp-searchPanes').find('span[title="In Progress"]').click();
    if (deliveryState){
        $('.dtsp-searchPanes').find('span[title="'+deliveryState+'"]').click();
    }
}

function filterAfterSubmit(deliveryState){
    let storedFilters = localStorage.getItem('filters');

    if (storedFilters) {
        let filtersArray = storedFilters.split('|');
        filtersArray.forEach(function(filter) {
            let $element = $('.dtsp-searchPanes').find('span[title*="' + filter + '"]');
            if ($element.length > 0) {
                $element.trigger($.Event('click', { ctrlKey: true }));
            }
        });
        localStorage.removeItem('filters');
    }
}

function saveFiltersOnSubmit(){
    $('form[name="edit_all_orders"]').on("submit", function(event) {
        event.preventDefault();
        let filters = [];
        $('.dtsp-filter-list .dtsp-name').each(function() {
            let filterValue = $(this).attr('title');
            if (filterValue) {
                filters.push(filterValue.trim());
            }
        });
        let filtersString = filters.join('|');
        localStorage.setItem('filters', filtersString);
        this.submit();
    });
}

function addFilterBadge(){
    let filterList = $(document).find('span.dtsp-filter-list');
    if (filterList.length){
        filterList.remove();
    }
    let filterListHtml = "<span class='dtsp-filter-list'> : </span>";

    let filterTitle = $(document).find('div.dtsp-title');

    $(filterTitle).after(filterListHtml);

    let selectedFilter = $(document).find('div.dtsp-searchPanes tr.selected');
    let filter = $(selectedFilter.find('span.dtsp-name'));
    filter.css("cursor", "pointer")
    $(filter.clone()).appendTo(".dtsp-filter-list");
}

function unselectFilterBadge(){
    let filtersList = $('span.dtsp-filter-list').find('span.dtsp-name');
    filtersList.each(function (key, item){
        $(item).on('click', function (e) {
            let title = $(item).attr('title');
            let filter = $('.dtsp-searchPanes').find('span[title="'+title+'"]');
            $(filter).click();
            $(this).remove();
        })
    })
}

export { preFilter, addFilterBadge, unselectFilterBadge, saveFiltersOnSubmit, filterAfterSubmit }