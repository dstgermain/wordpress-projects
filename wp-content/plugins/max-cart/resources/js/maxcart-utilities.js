/*global console*/

var maxcart = {};

(function maxCartUtilites($) {
    'use strict';

    maxcart.loading_add = function ($cover) {
        $cover.animate({opacity: '.3'}).append('<div class="loader"><i class="fa fa-spinner fa-pulse fa-2x"></i></div>');
    };
    maxcart.loading_remove = function () {
        var $loader = $('.loader');
        $loader.parent().animate({opacity: '1'}, 300);
        $loader.animate({opacity: '0'}, 300, 'swing', function () {
            $loader.remove();
        });
    };

    maxcart.localStorageSupport = function supports_html5_storage() {
        try {
            return 'localStorage' in window && window.localStorage !== null;
        } catch (e) {
            return false;
        }
    };

    maxcart.max_fancy_select = function ($self) {
        var options = $self.find('option'),
            selected = $self.find('option:selected'),
            type = $self.data('type'),
            fancy = '<div class="max-select">',
            items = '';

        if (options.length) {
            options.each(function () {
                var current = $(this),
                    current_val = current.val(),
                    current_txt = current.text();

                items += '<li data-value="' + current_val + '">' + current_txt + '</li>';
            });
        }

        if (selected.length) {
            fancy += '<button><span>' + selected.text() + '</span><i class="fa fa-chevron-down fa-fw"></i></button>';
        } else {
            fancy += '<button></span>' + $self.find('option:first-of-type').text() + '</span><i class="fa fa-chevron-down fa-fw"></i></button>';
        }

        fancy += '<ul class="items">' + items + '</ul>';

        fancy += '<ul class="auto-width">' + items + '</ul></div>';

        $self.after(fancy);

        var max_select = $self.next('.max-select'),
            max_btn = max_select.find('button');

        max_btn.on('click', function () {
            var btn = $(this);
            if (max_select.hasClass('open')) {
                btn.next().slideUp(function(){
                    max_select.removeClass('open');
                });
            } else {
                max_select.addClass('open');
                btn.next().slideDown();
            }
        });
        max_select.find('.items li').on('click', function () {
            var li = $(this),
                val = li.data('value'),
                option = '[value="' + val + '"]';
            max_btn.find('span').text(li.text());
            max_btn.next().slideUp(function () {
                max_select.removeClass('open');
                $self.find('option').attr('selected', false);
                $self.find(option).attr('selected', true);

                $self.trigger( 'max_selected', [ type, val ] );
            });
        });
    };

    maxcart.page_offset = 0;

    maxcart.endless = function () {
        maxcart.page_offset = maxcart.page_offset + 12;
        maxcart.loading_add( $('.max-product-wrapper') );
        maxcart.ajax_callback();
    };

    maxcart.ajax_callback = function () {
        var request = {
            _wpnonce: $('#verify_maxcart_ajax').val(),
            action: 'maxcart_get_posts',
            offset: maxcart.page_offset
            },
            load_more = $('.js-max-load-more');

        $('.max-filters').find('select, input').each(function () {
            var $self = $(this),
                type = $self.data('type'),
                input_type = $self.attr('type'),
                val = $self.val();

            if ((input_type === 'checkbox' && $self.attr('checked')) || (input_type !== 'checkbox')) {
                if (type && ( type === 'category' || type === 'company' || type === 'company_categories' ) ) {
                    if (!request[type]) {
                        request[type] = [];
                    }
                    request[type].push(val);
                } else if (type) {
                    request[type] = val;
                }
            }
        });

        $.ajax({
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: request
        }).success(function (data) {
            var response = {};

            try {
                response = JSON.parse(data);
            } catch (e) {
                response = {
                    success: false
                };
            }

            if (response.success) {
                if (maxcart.page_offset) {
                    $('.product-listing').append(response.body);
                } else {
                    $('.product-listing').html(response.body);
                }

                if (response.last) {
                    load_more.slideUp();
                } else if (!load_more.is(':visible')) {
                    load_more.slideDown();
                }
            }
        }).always(function () {
            maxcart.loading_remove();
        });
    };

    $(function maxCartUtilitiesDocReady() {
        var $max_select = $('.js-max-select');
        if ($max_select.length) {
            $max_select.each(function () {
                maxcart.max_fancy_select($(this));
            });
        }

        var $max_checkbox = $('.js-max-checkbox');
        if ($max_checkbox.length) {
            $(document).on('click', '.js-max-checkbox', function () {
                var $self = $(this),
                    $checkbox = $self.prev('input'),
                    is_checked = false;

                if ($self.hasClass('checked')) {
                    $checkbox.removeAttr('checked');
                    $self.removeClass('checked');
                } else {
                    $checkbox.attr('checked', true);
                    $self.addClass('checked');
                    is_checked = true;
                }

                $self.trigger( 'max_checked', [ is_checked, $checkbox.val() ] );
            });
        }

        $(document).on('click mouseover touchend', '.maxcart-quickcart-inner', function (e) {
            var $self = $(this),
                $quick_cart_table = $('.maxcart-quickcart-table');
            if ($self.closest('.maxcart-quickcart').hasClass('active') && $self.closest('.maxcart-quickcart').hasClass('clicked') && e.type === 'click') {
                $quick_cart_table.slideUp('300', function () {
                    $self.closest('.maxcart-quickcart').removeClass('active').removeClass('clicked');
                    $quick_cart_table.removeAttr('style');
                });
            } else {
                $self.closest('.maxcart-quickcart').addClass('active');
                $quick_cart_table.slideDown('300');
                if (e.type === 'click') {
                    $self.closest('.maxcart-quickcart').addClass('clicked');
                }
            }
        });

        $(document).on('click', function (e) {
            if (e.target.className !== 'maxcart-quickcart-table' && e.target.className !== 'maxcart-quickcart-inner') {
                $('.maxcart-quickcart-table').slideUp('300', function () {
                    $('.maxcart-quickcart-table').closest('.maxcart-quickcart').removeClass('active').removeClass('clicked');
                    $('.maxcart-quickcart-table').removeAttr('style');
                });
            }
        });

        var $max_endless = $('.js-max-load-more');
        if ($max_endless.length) {
            $(document).on('click', '.js-max-load-more', function () {
                maxcart.endless();
            });
        }
    });

})(jQuery);
