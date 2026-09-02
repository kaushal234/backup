$(document).ready(function() {
    let checkbox = $('#import_acl_keep_location')
    let location = $('#import_acl_location')

    if(checkbox.is(':checked')){
        location.prop('disabled', true)
    }
    checkbox.on('click', function(){
        checkbox.is(':checked') ? location.prop('disabled', true) : location.prop('disabled', false);
    })
})