$( document ).ready(function() {
    $('.activity ul.nav a').click(function(){
        $('.activity .nav-tabs li').each(function() {
            $(this).removeClass('active');
        })
        $(this).parent().addClass('active');
        $('.activity .tab-content .tab-pane').removeClass('active');
        $($(this).attr('href')).addClass('active');
    });
});