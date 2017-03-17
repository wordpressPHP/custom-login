jQuery(document).ready(function ($) {

    var CL_Timeout = 200,
        dashboard_primary = $('#dashboard_primary');

    if (!dashboard_primary.find('.rss-widget').eq(1).length) {
        CL_Timeout = 2500;
    }

    if (!cl_admin_dashboard.is_active) {
        setTimeout(function () {
            dashboard_primary.find('.rss-widget').eq(1).find('ul').append('<a class="rsswidget" href="' +
                cl_admin_dashboard.feed_url + '">' + cl_admin_dashboard.site_title + ': ' +
                cl_admin_dashboard.feed_title + '</a>');
        }, CL_Timeout);
    }

    /**
     *** Toggles ************************
     ************************************
     ************************************
     */
    $('[data-toggle]').each(function (index, element) {
        $(element).on('click', function () {
            $($(element).data('toggle')).slideToggle('fast');
        });
    });

});