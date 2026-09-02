function removeElement(e) {
    e.preventDefault();
    var tr = jQuery(this).parent().parent();
    tr.prev().remove();
    tr.prev().remove();
    tr.remove();
}

function addCollectionDeleteElement() {
    var collections = jQuery('div[data-prototype]');
    if (collections.length > 0) {
        jQuery('.deleteElement').remove();
        collections.each(function(){
            var container = jQuery(this).parent('td');
            var inputs = container.find('table.collection input[type=text]');
            var deleteMe = '<a href="#" class="deleteElement">X</a>';
            jQuery(inputs).after(deleteMe);
        });
        jQuery('.deleteElement').on('click', removeElement);
    }
}

function addCollectionElement(e) {
    e.preventDefault();
    var addLink = jQuery(this);
    var container = addLink.parent('td');
    var prototypeDiv = container.find('div[data-prototype]');
    var prototype = prototypeDiv.attr('data-prototype');
    var table = container.find('table.collection');
    var rows = table.find('tr');
    var inputs = table.find('input[type=text]');
    var newForm = prototype.replace(/__name__label__/g, inputs.length).replace(/__name__/g, inputs.length);

    if (rows.length > 0) {
        jQuery(rows[rows.length - 1]).after(newForm);
    } else {
        table.append(newForm);
    }
    addCollectionDeleteElement();
}

function setupCollection() {
    var collection = jQuery(this);
    var container = collection.parent('td');
    var addLink = jQuery('<a href="#" class="addNew">Add</a>');
    container.append(addLink);
    addCollectionDeleteElement();
}

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
    var collections = jQuery('div[data-prototype]');
    if (collections.length > 0) {
        collections.each(setupCollection);
        jQuery('.addNew').on('click', addCollectionElement);
    }

    if (jQuery('.organisation button[data-people-id]').length > 0) {
        jQuery('div#page').on('click', organisationSwitch);
    }
});
