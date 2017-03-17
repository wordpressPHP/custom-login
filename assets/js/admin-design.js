(function ($, undefined) {

    $(document).ready(function () {

        var $body = $('body');

        /**
         *** callback_html ******************
         ************************************
         ************************************
         *
         $.each( $('div[class^="section-custom_login"]'), function( index, value ) {
        $(value).parents('tr').find('th').prop('colspan','2').append('<hr>');
        $(value).parent('td').remove();
        }); //*/

        /**
         *** callback_raw *******************
         ************************************
         ************************************
         */
        $.each($('div[class="raw-html"]'), function (index, value) {
            $(value).parents('tr').find('td').prop('colspan', '2');
            $(value).parents('tr').find('th').remove();
        });

        /**
         *** callback_text_array ************
         ************************************
         ************************************
         */
        $body.on('click', 'a[class^="button docopy-"]', function (e) {
            e.preventDefault();

            $this = $(this).prev().children();
            var clone = $('input[id="' + $this.children().prop('id') + '"]');
            var value = clone.data('key');
            var newValue = parseInt(value) + 1;

            var newInput = $this.last().clone();
            newInput.insertAfter(clone.parent().last());
            newInput.children().val('').data('key', newValue);
            return false;
        });
        $body.on('click', 'a[class^="button dodelete-"]', function (e) {
            e.preventDefault();

            $(this).parent().remove();
        });

        /**
         *** callback_colorpicker ***********
         ************************************
         ************************************
         */
        if ('undefined' !== typeof cl_settings_api.colorpicker) {
            $.each(cl_settings_api.colorpicker, function (index, value) {
                $this = value.section + '[' + value.id;
                $('input[name="' + $this + ']"]').wpColorPicker();

                if ($('select[name="' + $this + '_opacity]"]').hasClass('hidden')) {
                    $('select[name="' + $this + '_opacity]"]').removeClass('hidden').chosen().addClass('hidden');
                } else {
                    $('select[name="' + $this + '_opacity]"]').chosen();
                }
                $('select[name="' + $this + '_opacity]"]').trigger('chosen:updated');

                /**
                 * .replace @ref    http://stackoverflow.com/a/3812077/558561
                 */
                var str = $this + '_opacity';
                if (!$('input[name="' + $this + '_checkbox]"]').is(':checked')) {
                    $('#' + str.replace(/[\[\]]/g, '_') + '__chosen').hide();
                }

                $('input[name="' + $this + '_checkbox]"]').on('change', function () {
                    $('#' + str.replace(/[\[\]]/g, '_') + '__chosen').toggle();
                });
            });
        }

        /**
         *** callback_file ******************
         ************************************
         ************************************
         */
        if ('undefined' !== typeof  cl_settings_api.file) {
            $.each(cl_settings_api.file, function (index, value) {
                // WP 3.5+ uploader
                var file_frame;
                var wp_media_post_id = wp.media.model.settings.post.id;
                var set_to_post_id = 0;
                window.formfield = '';

                $(document.body).on('click', 'input[type="button"].button.' + value.id + '-browse', function (e) {
                    e.preventDefault();

                    $this = $(this);
                    window.formfield = $this.closest('td');

                    // If the media frame already exists, reopen it.
                    if (file_frame) {
                        file_frame.uploader.uploader.param('post_id', set_to_post_id);
                        file_frame.open();
                        return;
                    } else {
                        // Set the wp.media post id so the uploader grabs the ID we want when initialised
                        wp.media.model.settings.post.id = set_to_post_id;
                    }

                    // Create the media frame.
                    file_frame = wp.media.frames.file_frame = wp.media({
                        frame: 'post',
                        state: 'insert',
                        title: $this.data('uploader_title'),
                        button: {
                            text: $this.data('uploader_button_text')
                        },
                        library: {
                            type: 'image'
                        },
                        multiple: false  // Set to true to allow multiple files to be selected
                    });

                    file_frame.on('menu:render:default', function (view) {
                        // Store our views in an object.
                        var views = {};

                        // Unset default menu items
                        view.unset('library-separator');
                        view.unset('gallery');
                        view.unset('featured-image');
                        view.unset('embed');

                        // Initialize the views in our view object.
                        view.set(views);
                    });

                    // When an image is selected, run a callback.
                    file_frame.on('insert', function () {
                        var attachment = file_frame.state().get('selection').first().toJSON();

                        //	console.log(attachment);
                        //	console.log(window.formfield.find('input[type="text"]').attr('id'));

                        window.formfield.find('input[type="text"]').val(attachment.url);
                        window.formfield.find('#' + value.id + '_preview').html('<div class="img-wrapper" style="width:250px"><img src="' + attachment.url + '" alt="" ><a href="#" class="remove_file_button" rel="' + value.id + '">Remove Image</a></div>');

                        // @since 3.0.1
                        // @updated	3.0.3
                        if (window.formfield.find('input[type="text"]').attr('id') == 'custom_login_design[logo_background_url]') {
                            window.formfield.parents('table').find('input[name="custom_login_design[logo_background_size_width]"]').val(attachment.width);
                            window.formfield.parents('table').find('input[name="custom_login_design[logo_background_size_height]"]').val(attachment.height);
                        }

                    });

                    // Finally, open the modal
                    file_frame.open();
                });

                $('input[type="button"].button.' + value.id + '-clear').on('click', function (e) {
                    e.preventDefault();
                    $(this).closest('td').find('input[type="text"]').val('');
                    $(this).closest('td').find('#' + $(this).prop('id').replace('_clear', '_preview') + ' div.image').remove();
                });
                $('a.remove_file_button').on('click', function (e) {
                    e.preventDefault();
                    $(this).closest('td').find('input[type="text"]').val('');
                    $(this).parent().slideUp().remove();
                });
            });
        }

    });

}(jQuery));