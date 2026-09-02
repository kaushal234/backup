function organisationSwitch(e) {
    var target = jQuery(e.target);
    var peopleId = target.attr('data-people-id');

    if (undefined !== peopleId) {

        e.preventDefault();

        if (target.hasClass('open')) {
            jQuery('#under-' + peopleId).remove();
            target.removeClass('open').html('+');
        } else {
            jQuery.ajax({
                    url: Routing.generate('directory_organisation_business_unit_people_ajax', { peopleId: peopleId })
                })
                .done(function(data) {
                    jQuery('#people-' + peopleId).append(data);
                    target.addClass('open').html('-');
                });
        }
    }
}

jQuery(document).ready(function() {
    if (jQuery('.organisation button[data-people-id]').length > 0) {
        jQuery('div#page-wrapper').on('click', organisationSwitch);
    }
});
