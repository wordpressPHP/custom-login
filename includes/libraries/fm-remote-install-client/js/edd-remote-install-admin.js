jQuery(document).ready(function ($) {

    var downloadButton,
        doingAJAX,
        data = {
            nonce: edd_ri_options.nonce
        };

    if (edd_ri_options.skipplugincheck != true) {

        $('a[data-edd-install]').each(function () {

            downloadButton = $(this);
            var statusContainer = $(downloadButton).parents('.plugin-card').find('[id^="progress-container"]').find('.eddri-status');

            data.action = 'edd-ri-check-plugin-status';
            data.download = downloadButton.data('edd-install');
            data.basename = downloadButton.data('edd-plugin-basename');

            $.post(ajaxurl, data, function (res) {

                if (res == 'installed') {
                    statusContainer.text(edd_ri_options.i18n.inactive).addClass('eddri-inactive');
                    $(downloadButton).text(edd_ri_options.i18n.activate);
                } else if (res == 'active') {
                    statusContainer.text(edd_ri_options.i18n.active).addClass('eddri-active');
                    $(downloadButton).text(edd_ri_options.i18n.deactivate);
                }
            });
        });
    }

    $('body').on('click', 'a[data-edd-install]', function (e) {
        e.preventDefault();

        downloadButton = $(this);
        var statusContainer = $(this).parents('ul.plugin-action-buttons').find('.eddri-status'),
            progressContainer = $(this).parents('.plugin-card').find('[id^="progress-container"]').find('.eddri-status');

        if (downloadButton.text() == edd_ri_options.i18n.activate) {

            data.action = 'edd-ri-activate-plugin';
            data.download = downloadButton.data('edd-install');
            data.basename = downloadButton.data('edd-plugin-basename');

            $.post(ajaxurl, data, function (res) {

                if (res == 'activated') {
                    $(statusContainer).text(edd_ri_options.i18n.active).addClass('eddri-active');
                    $(downloadButton).text(edd_ri_options.i18n.deactivate);
                } else {
                    $(statusContainer).addClass('eddri-error').text("Error activating.");
                }
            });

        } else if (downloadButton.text() == edd_ri_options.i18n.deactivate) {

            data.action = 'edd-ri-deactivate-plugin';
            data.download = downloadButton.data('edd-install');
            data.basename = downloadButton.data('edd-plugin-basename');

            $.post(ajaxurl, data, function (res) {

                if (res == 'deactivated') {
                    $(statusContainer).text(edd_ri_options.i18n.inactive).removeClass('eddri-active success finished')
                        .addClass('eddri-inactive');
                    $(downloadButton).text(edd_ri_options.i18n.activate);
                } else {
                    $(statusContainer).addClass('eddri-error').text("Error deactivating.");
                }
            });

        } else {

            data.action = 'edd-ri-check-remote-install';
            data.download = downloadButton.data('edd-install');
            data.basename = downloadButton.data('edd-plugin-basename');

            progressContainer.show();
            progressContainer.progressInitialize()
                .progressStart()
                .attr({'data-loading': "Requesting package..."});

            $.post(ajaxurl, data, function (res) {
                console.log(res);
                res = $.parseJSON(res);
                if (res == '0') {
                    // Free download found

                    data.action = 'edd-ri-do-remote-install';

                    progressContainer.progressSet(50)
                        .attr({'data-loading': "Found package. Installing..."});

                    $.post(ajaxurl, data, function (res) {
                        $(downloadButton).text(edd_ri_options.i18n.deactivate);
                        return progressContainer.progressFinish(res, downloadButton);
                    });

                } else if (res == '1') {
                    // License key required to continue
                    progressContainer.validateLicense(downloadButton);
                } else {
                    return progressContainer.progressFinish(res, downloadButton);
                }
            });
        }
    });

    /**
     * Progress meter functionality defined in jQuery plugins.
     */
    $.fn.validateLicense = function (downloadButton) {

        var statusContainer = $(downloadButton).parents('.plugin-card').find('[id^="progress-container"]').find('.eddri-status'),
            licenseID,
            licenseInput;

        licenseID = 'license-input-' + sanitize_title_with_dashes($(downloadButton).data('edd-install'));

        // Pause auto-updating of progress bar and create license key input field
        statusContainer.progressStop(40)
            .attr({'data-loading': "Enter license key to continue:"})
            .after('<input id="' + licenseID + '" placeholder="License key"/>')
            .off('click')
            .removeClass('success failure');

        licenseInput = $('input[id="' + licenseID + '"]');

        licenseInput.on('click', function () {

            statusContainer.attr({'data-loading': "Click here to proceed."});

            statusContainer.click(function (e) {
                e.stopPropagation();
                validPost();
            });

            downloadButton.click(function (e) {
                e.stopPropagation();
                validPost();
            });

            $(document).keypress(function (e) {
                if (e.which == 13) {
                    e.preventDefault();
                    validPost();
                }
            });

            function validPost() {
                var license = licenseInput.val();
                if (!license) return false;

                data.action = 'edd-ri-do-remote-install';
                data.download = downloadButton.data('edd-install');
                data.basename = downloadButton.data('edd-plugin-basename');
                data.license = license;
                data.url = edd_ri_options.url;

                //licenseInput.remove();

                statusContainer.progressStart().progressSet(50)
                    .attr({'data-loading': "Validating license..."});

                if (!doingAJAX) {
                    $.post(ajaxurl, data, function (res) {
                        doingAJAX = true;
                        statusContainer.progressFinish(res, downloadButton, license);
                    });
                }
            }
        });
    };

    $.fn.progressInitialize = function () {
        var button = this;
        var progress = 0;

        // Add markup for the progress bar.
        var bar = $('<span class="tz-bar background-horizontal">').appendTo(button);

        button.on('progress', function (e, val, absolute, finished) {
            var $finished = finished;

            // Make sure button has `in-progress` class when initialized.
            // And that local var `progress` = 0 to start.
            // Then show the progress bar.
            if (!button.hasClass('in-progress')) {
                button.removeClass('finished').addClass('in-progress');
                progress = 0;
                bar.show();
            }

            if (absolute) {
                progress = val;
            } else if (progress >= 100) {
                progress = 100;
                $finished = true;
            } else {
                progress += val;
            }

            if ($finished) {
                button.removeClass('in-progress').addClass('finished');

                bar.delay(500).fadeOut(function () {
                    button.trigger('progress-finish');
                    setProgress(0);
                });
            }

            setProgress(progress);
        });

        function setProgress(percentage) {
            bar.filter('.background-horizontal,.background-bar').width(percentage + '%');
            bar.filter('.background-vertical').height(percentage + '%');
        }

        return button;
    };

    $.fn.progressStart = function () {
        var button = this;
        var last_progress = new Date().getTime();

        if (button.hasClass('in-progress')) {
            // Don't start it a second time!
            return button;
        }

        button.on('progress', function () {
            last_progress = new Date().getTime();
        });

        // Every half a second check whether the progress
        // has been incremented in the last two seconds

        var interval = window.setInterval(function () {

            // Check every half-second to see whether
            // progress has incremented in past 2 seconds.
            if (new Date().getTime() > 2000 + last_progress && !button.hasClass('stopped')) {

                // There has been no activity for 2s. Increment the progress
                // bar a little bit to show that something is happening.
                button.progressIncrement(5);
            }

        }, 500);

        button.on('progress-finish', function () {
            window.clearInterval(interval);
        }).progressIncrement(10);
        return button;
    };

    $.fn.progressSet = function (val) {
        var button = this;
        var finished = false;
        val = val || 10;

        if (button.hasClass('stopped')) {
            button.removeClass('stopped');
        }

        if (val >= 100) {
            finished = true;
        }

        button.trigger('progress', [val, true, finished]);
        return button;
    };

    $.fn.progressIncrement = function (val) {
        var button = this;
        val = val || 10;
        button.trigger('progress', [val]);
        return button;
    };

    $.fn.progressStop = function (val) {
        var button = this;
        button.progressSet(val).addClass('stopped');
        return button;
    };

    $.fn.progressFinish = function (res, downloadButton, license) {

        var statusContainer = $(downloadButton).parents('.plugin-card').find('[id^="progress-container"]').find('.eddri-status');

        statusContainer.progressSet(100);

        if (res === 'invalid') {
            statusContainer.attr({'data-finished': "Invalid License"});

            setTimeout(function () {
                statusContainer.validateLicense(downloadButton);
            }, 1200);

        } else if (res.search('error') > 0) {

            // If there was an unknown error, try to run the install manually

            statusContainer.attr({'data-finished': "Unknown error. Redirecting..."})
                .addClass('eddri-error');

            data.action = 'edd-ri-do-manual-install';
            data.download = downloadButton.data('edd-install');
            data.basename = downloadButton.data('edd-plugin-basename');
            data.license = license;

            //$.post(ajaxurl, data, function (res) {
            //    //			console.log(res);
            //    window.location.href = res;
            //});

        } else if (res.search('already exists') > 0) {
            statusContainer.attr({'data-finished': "Error: Already installed"})
                .addClass('eddri-error');
        } else if (res.search('not exist') > 0) {
            statusContainer.attr({'data-finished': "Error: Plugin file does not exist."})
                .addClass('eddri-error');
        } else if (res.search('installed successfully') > 0) {
            statusContainer.attr({'data-finished': "Success!"})
                .addClass('success');
            $(downloadButton).text(edd_ri_options.i18n.deactivate);
        } else {

            // If there was an unknown error, try to run the install manually

            //statusContainer.attr({'data-finished': "Unknown error. Redirecting..."})
            //    .addClass('eddri-error');
            //
            //data.action = 'edd-ri-do-manual-install';
            //data.download = downloadButton.data('edd-install');
            //data.license = license;
            //
            //$.post(ajaxurl, data, function (res) {
            //    window.location.href = res;
            //});
        }

        doingAJAX = false;
        return statusContainer;
    };

    var sanitize_title_with_dashes = function (str) {
        return str.replace(/\s+/g, '-').toLowerCase();
    }
});