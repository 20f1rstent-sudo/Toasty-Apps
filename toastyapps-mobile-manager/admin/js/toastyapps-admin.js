/**
 * ToastyApps Mobile Manager - Admin JavaScript
 *
 * @package ToastyApps_Mobile_Manager
 */

(function($) {
    'use strict';

    /**
     * ToastyApps Admin Module
     */
    var ToastyAppsAdmin = {

        /**
         * Initialize
         */
        init: function() {
            this.initColorPickers();
            this.initMediaUploaders();
            this.initClipboard();
            this.initSortable();
            this.initConfirmDialogs();
        },

        /**
         * Initialize WordPress color pickers
         */
        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.color-picker').wpColorPicker({
                    change: function(event, ui) {
                        // Trigger change event for live preview
                        $(this).trigger('colorchange', ui.color.toString());
                    },
                    clear: function() {
                        $(this).trigger('colorchange', '');
                    }
                });
            }
        },

        /**
         * Initialize media uploaders
         */
        initMediaUploaders: function() {
            var self = this;

            // Generic media upload handler
            $(document).on('click', '.toastyapps-upload-btn', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var inputId = $btn.data('input');
                var previewId = $btn.data('preview');
                var type = $btn.data('type') || 'image';

                var mediaUploader = wp.media({
                    title: type === 'video' ? toastyapps.strings.select_video : toastyapps.strings.select_image,
                    button: {
                        text: type === 'video' ? toastyapps.strings.use_video : toastyapps.strings.use_image
                    },
                    library: {
                        type: type
                    },
                    multiple: false
                });

                mediaUploader.on('select', function() {
                    var attachment = mediaUploader.state().get('selection').first().toJSON();

                    $('#' + inputId).val(attachment.id);

                    if (previewId) {
                        self.updatePreview(previewId, attachment, type);
                    }
                });

                mediaUploader.open();
            });

            // Generic remove media handler
            $(document).on('click', '.toastyapps-remove-btn', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var inputId = $btn.data('input');
                var previewId = $btn.data('preview');

                $('#' + inputId).val('');

                if (previewId) {
                    $('#' + previewId).html(
                        '<div class="placeholder">' +
                        '<span class="dashicons dashicons-format-image"></span>' +
                        '<p>No media selected</p>' +
                        '</div>'
                    );
                }
            });
        },

        /**
         * Update media preview
         */
        updatePreview: function(previewId, attachment, type) {
            var $preview = $('#' + previewId);

            if (type === 'video') {
                $preview.html('<video src="' + attachment.url + '" controls width="320"></video>');
            } else {
                $preview.html('<img src="' + attachment.url + '" alt="">');
            }
        },

        /**
         * Initialize clipboard functionality
         */
        initClipboard: function() {
            $(document).on('click', '.copy-btn, .copy-api-key', function(e) {
                e.preventDefault();

                var $btn = $(this);
                var text = $btn.data('copy') || $btn.data('clipboard-target');

                if (text && text.startsWith('#')) {
                    text = $(text).text() || $(text).val();
                }

                if (text) {
                    navigator.clipboard.writeText(text).then(function() {
                        var originalHtml = $btn.html();
                        $btn.html('<span class="dashicons dashicons-yes"></span>');

                        setTimeout(function() {
                            $btn.html(originalHtml);
                        }, 1500);
                    }).catch(function(err) {
                        console.error('Failed to copy:', err);
                    });
                }
            });
        },

        /**
         * Initialize sortable lists
         */
        initSortable: function() {
            if ($.fn.sortable) {
                $('.sortable-list').sortable({
                    handle: '.drag-handle',
                    placeholder: 'sortable-placeholder',
                    update: function(event, ui) {
                        var $list = $(this);
                        var order = [];

                        $list.find('tr').each(function() {
                            order.push($(this).data('id'));
                        });

                        // Save order via AJAX
                        $.post(toastyapps.ajax_url, {
                            action: 'toastyapps_update_reel_order',
                            nonce: toastyapps.nonce,
                            order: order
                        });
                    }
                });
            }
        },

        /**
         * Initialize confirm dialogs
         */
        initConfirmDialogs: function() {
            $(document).on('click', '[data-confirm]', function(e) {
                var message = $(this).data('confirm') || toastyapps.strings.confirm_delete;

                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        }
    };

    /**
     * Dashboard Module
     */
    var Dashboard = {

        init: function() {
            this.initCharts();
            this.initRefresh();
        },

        initCharts: function() {
            // Charts could be added here with Chart.js or similar
        },

        initRefresh: function() {
            // Auto-refresh dashboard stats
            if ($('.toastyapps-dashboard').length) {
                // Could add periodic refresh here
            }
        }
    };

    /**
     * Notifications Module
     */
    var Notifications = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Live preview for notification form
            $('#notification_title').on('input', function() {
                var title = $(this).val() || 'Notification Title';
                $('#preview-title').text(title);
            });

            $('#notification_body').on('input', function() {
                var body = $(this).val() || 'Notification message will appear here...';
                $('#preview-body').text(body);
            });

            // Character count
            $('#notification_title, #notification_body').on('input', function() {
                var $input = $(this);
                var maxLength = parseInt($input.attr('maxlength'), 10);
                var currentLength = $input.val().length;

                var $counter = $input.siblings('.char-counter');
                if (!$counter.length) {
                    $counter = $('<span class="char-counter"></span>');
                    $input.after($counter);
                }

                $counter.text(currentLength + '/' + maxLength);

                if (currentLength >= maxLength * 0.9) {
                    $counter.addClass('warning');
                } else {
                    $counter.removeClass('warning');
                }
            });
        }
    };

    /**
     * Branding Module
     */
    var Branding = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Live preview updates
            $('#app_name').on('input', function() {
                $('#preview-app-name').text($(this).val() || 'App Name');
            });

            $('#tagline').on('input', function() {
                var tagline = $(this).val();
                var $tagline = $('#preview-tagline');

                if (tagline) {
                    if (!$tagline.length) {
                        $('#preview-app-name').after(
                            '<small id="preview-tagline" style="color: rgba(255,255,255,0.8);"></small>'
                        );
                        $tagline = $('#preview-tagline');
                    }
                    $tagline.text(tagline);
                } else {
                    $tagline.remove();
                }
            });

            // Color updates via wpColorPicker change event
            $(document).on('colorchange', '.color-picker', function(e, color) {
                self.updateColorPreview($(this).attr('name'), color);
            });
        },

        updateColorPreview: function(colorName, color) {
            switch (colorName) {
                case 'primary_color':
                    $('#preview-header').css('background-color', color);
                    $('.preview-btn.primary').css('background-color', color);
                    break;
                case 'secondary_color':
                    $('.preview-btn.secondary').css('background-color', color);
                    break;
                case 'accent_color':
                    $('.preview-alert').css('background-color', color);
                    break;
                case 'text_color':
                    $('.preview-text').css('color', color);
                    break;
                case 'background_color':
                    $('#preview-screen').css('background-color', color);
                    break;
            }

            // Update gradient card
            var primary = $('#primary_color').val();
            var secondary = $('#secondary_color').val();
            $('.preview-card').css('background', 'linear-gradient(135deg, ' + primary + ', ' + secondary + ')');
        }
    };

    /**
     * Settings Module
     */
    var Settings = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Toggle password visibility
            $('#toggle-server-key').on('click', function() {
                var $input = $('#fcm_server_key');
                var type = $input.attr('type');

                $input.attr('type', type === 'password' ? 'text' : 'password');
                $(this).find('.dashicons')
                    .toggleClass('dashicons-visibility dashicons-hidden');
            });

            // Regenerate API key
            $('#regenerate-api-key').on('click', function() {
                if (!confirm(toastyapps.strings.confirm_regenerate || 'Are you sure you want to regenerate the API key? Your mobile app will need to be updated with the new key.')) {
                    return;
                }

                var $btn = $(this);
                $btn.prop('disabled', true);

                $.post(toastyapps.ajax_url, {
                    action: 'toastyapps_regenerate_api_key',
                    nonce: toastyapps.nonce
                }, function(response) {
                    if (response.success) {
                        $('#api_key_display').val(response.data.api_key);
                        $('.copy-btn[data-copy]').data('copy', response.data.api_key);
                        alert(response.data.message);
                    } else {
                        alert(response.data.message || 'An error occurred');
                    }
                    $btn.prop('disabled', false);
                }).fail(function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false);
                });
            });
        }
    };

    /**
     * Media Reels Module
     */
    var MediaReels = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            var self = this;

            // Delete reel
            $(document).on('click', '.delete-reel', function() {
                if (!confirm(toastyapps.strings.confirm_delete)) {
                    return;
                }

                var $btn = $(this);
                var reelId = $btn.data('id');

                $btn.prop('disabled', true);

                $.post(toastyapps.ajax_url, {
                    action: 'toastyapps_delete_reel',
                    nonce: toastyapps.nonce,
                    reel_id: reelId
                }, function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                            self.checkEmptyState();
                        });
                    } else {
                        alert(response.data.message || 'An error occurred');
                        $btn.prop('disabled', false);
                    }
                }).fail(function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false);
                });
            });
        },

        checkEmptyState: function() {
            if ($('#reels-list tr').length === 0) {
                location.reload();
            }
        }
    };

    /**
     * Locations Module
     */
    var Locations = {

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Delete location
            $(document).on('click', '.delete-location', function() {
                if (!confirm(toastyapps.strings.confirm_delete)) {
                    return;
                }

                var $btn = $(this);
                var locationId = $btn.data('id');

                $btn.prop('disabled', true);

                $.post(toastyapps.ajax_url, {
                    action: 'toastyapps_delete_location',
                    nonce: toastyapps.nonce,
                    location_id: locationId
                }, function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || 'An error occurred');
                        $btn.prop('disabled', false);
                    }
                }).fail(function() {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false);
                });
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Initialize main admin module
        ToastyAppsAdmin.init();

        // Initialize page-specific modules based on body class or page
        if ($('.toastyapps-dashboard').length) {
            Dashboard.init();
        }

        if ($('#notification-form').length) {
            Notifications.init();
        }

        if ($('.toastyapps-branding-grid').length) {
            Branding.init();
        }

        if ($('.nav-tab-wrapper').length && $('[name="fcm_server_key"]').length) {
            Settings.init();
        }

        if ($('#reels-list').length || $('#upload-reel-video').length) {
            MediaReels.init();
        }

        if ($('.delete-location').length) {
            Locations.init();
        }
    });

})(jQuery);
