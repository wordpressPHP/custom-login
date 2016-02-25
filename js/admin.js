(function ($, undefined) {

    $(document).ready(function () {

        /**
         * Global variables
         *
         * @var string
         */
        var $this,
            activetab,
            clicked_group,
            form;

        /**
         * @type {*|HTMLElement}
         */
        var cl_container = $('.cl-container'),
            cl_header = $('.cl-header'),
            cl_save = $('input[id="cl_save"]');

        /**
         * Match the header with the WP-admin user color selection.
         */
        cl_header.css('background-color', $('#adminmenuwrap').css('background-color'));
        cl_header.contrastColor();
        cl_container.addClass('loaded');

        /**
         *** Active *************************
         ************************************
         ************************************
         */
        //$('span.tgl_input').replaceWith($('input[id="custom_login_settings[general][active]"]').clone());
        //
        //$(document).on('click change', 'input[id="custom_login_settings[general][active]"]', function () {
        //    $('input[id="custom_login_settings[general][active]"]').prop('checked', this.checked);
        //});

        /**
         *** Sidebar Nav + Main Group *******
         ************************************
         ************************************
         */
        var cl_main_group = $('.cl-main .group'),
            cl_sections_menu = $('.cl-sections-menu');

        cl_main_group.hide();

        if ('undefined' !== typeof localStorage) {
            activetab = localStorage.getItem('activetab');
        }

        if (activetab != '' && $(document.getElementById(activetab)).length) {
            $(document.getElementById(activetab)).fadeIn();
            cl_save.val('Save ' + $('a[data-tab-id="' + activetab + '"]').text());
        }
        else {
            var first_group = cl_main_group.first();
            first_group.fadeIn();
            cl_save.val('Save ' + $('.cl-sidebar .cl-sections-menu li:first a').text());
        }

        if (activetab != '' && cl_sections_menu.find('a[data-tab-id="' + activetab + '"]').length) {
            cl_sections_menu.find('a[data-tab-id="' + activetab + '"]').addClass('active');
        }
        else {
            cl_sections_menu.find('a').first().addClass('active');
        }
        // on.click event
        cl_sections_menu.find('a').on('click', function (e) {
            $this = $(this);

            if ('javascript:;' !== $this.attr('href')) {
                if ('undefined' !== typeof localStorage) {
                    localStorage.setItem('activetab', '');
                }
                return true;
            }

            clicked_group = $this.data('tab-id');
            cl_save.val('Save ' + $('a[data-tab-id="' + clicked_group + '"]').text());
            cl_sections_menu.find('a').removeClass('active');
            $this.addClass('active').blur();

            if ('undefined' !== typeof localStorage) {
                localStorage.setItem('activetab', clicked_group);
            }

            cl_main_group.hide();
            $(document.getElementById(clicked_group)).fadeIn();
            e.preventDefault();
        });

        /**
         *** Sticky *************************
         ************************************
         ************************************
         */
        var sticky = $('#cl-sticky'),
            wpadminbar = $('#wpadminbar');

        sticky.sticky({
            topSpacing: wpadminbar.length ? wpadminbar.height() : 0,
            getWidthFrom: cl_container
        });

        $(window).scroll(function () {
            if ($(window).scrollTop() + $(window).height() > $(document).height() - 200) {
                sticky.hide();
            } else {
                sticky.show();
            }
        });

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

        /**
         *** Form Submit ********************
         ************************************
         ************************************
         */
        $(document.body).on('click', '#cl-sticky input[id="cl_save"]', function (e) {
            $this = $(this);
            form = $('.cl-main > div.group:visible > form');
            $this.attr('form', form.attr('id'));
            form.submit();
        }); //*/

        /**
         *** CodeMirror *********************
         ************************************
         ************************************
         */
        if ('undefined' !== typeof CodeMirror) {
            $('textarea').each(function () {
                if ($(this).data('codemirror')) {
                    CodeMirror.fromTextArea(document.getElementById($(this).attr('id')), {
                        lineNumbers: true,
                        mode: $(this).data('type') ? $(this).data('type') : 'htmlmixed'
                    });
                }
            });
        }

        /**
         *** Callback Fields Types **********
         ************************************
         ************************************
         */
        $('div.field-type-html-break').each(function () {
            $(this).parents('tr').find('th').wrapInner('<h4/>');
        });

    }); // (document)

    /**
     * Helper function to create contracting color.
     *
     * @ref http://codeitdown.com/jquery-color-contrast/
     */
    $.fn.contrastColor = function () {
        return this.each(function () {
            var bg = $(this).css('background-color');
            //get r,g,b and decide
            var rgb = bg.replace(/^(rgb|rgba)\(/, '').replace(/\)$/, '').replace(/\s/g, '').split(',');
            var yiq = ((rgb[0] * 299) + (rgb[1] * 587) + (rgb[2] * 114)) / 1000;
            if (yiq >= 128) {
                $(this).children().css('color', '#111111');
            } else {
                $(this).children().css('color', '#ffffff');
            }
        });
    };

}(jQuery));