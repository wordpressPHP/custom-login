(function ($) {

  "use strict";

  var CustomLoginDashboard = {
    timeout: 200,
    dashboardPrimary: $('#dashboard_primary'),
    clObject: window.cl_admin_dashboard,

    init: function () {
      if (!this.dashboardPrimary.find('.rss-widget').eq(1).length) {
        this.timeout = 2500;
      }

      if (!this.clObject.is_active) {
        setTimeout(this.timeoutCallback(), this.timeout);
      }

      $('[data-toggle]').each(function (index, element) {
        $(element).on('click', function () {
          $($(element).data('toggle')).slideToggle('fast');
        });
      });
    },

    timeoutCallback: function () {
      this.dashboardPrimary.find('.rss-widget')
        .eq(1)
        .find('ul')
        .append('<a class="rsswidget" href="' + this.clObject.feed_url + '">' + this.clObject.site_title + ': ' + this.clObject.feed_title + '</a>');
    }
  };

  $(document).ready(CustomLoginDashboard.init());

}(jQuery));
