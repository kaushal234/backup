/*
Get the hash of url and set corresponding tab active.
 */
$(document).ready(function() {
    let url = window.location.href;
    let activeTab = url.substring(url.indexOf("#") + 1);
    if (activeTab && activeTab !== window.location.href) {
        let activeTabElement = $("#" + activeTab);
        if (window.location.hash && activeTabElement.length) {
            $(".tab-pane").removeClass("active in");
            activeTabElement.addClass("active in");
            $('a[href="#' + activeTab + '"]').tab('show');
        }
    }
});
