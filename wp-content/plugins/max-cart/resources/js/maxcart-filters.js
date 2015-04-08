/**
 * Created by dstgermain on 3/29/15.
 */
/* global maxcart, console */
(function maxCartFilters($) {
    'use strict';
    maxcart.category_filter = function($self, val, checked) {
        var filter = '.js-add-children_' + val,
            filter_ul = filter + ' > ul',
            $filter_ul = $(filter_ul),
            $section = $(filter);

        if (checked && !$section.hasClass('has-children')) {
            $section.addClass('has-children');
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'maxcart_get_categories',
                    _wpnonce: $('#verify_maxcart_ajax').val(),
                    category: val
                }
            }).success(function (data) {
                var response = {};

                try {
                    response = JSON.parse(data);
                } catch (e) {
                    response = {
                        success: false
                    };
                }

                if (response.success && (response.categories && response.categories.length)) {
                    var categories = '<ul>';

                    for (var i = 0; i < response.categories.length; i++) {
                        categories += '<li class="js-add-children_' + response.categories[i].id + '">';
                        categories += '<input type="checkbox" data-type="category" class="hidden" value="' + response.categories[i].id + '"/>';
                        categories += '<label class="js-max-checkbox max-checkbox">' + response.categories[i].name + '</label>';
                        categories += '</li>';
                    }

                    categories += '</ul>';
                    $section.append(categories);
                }
            });
        } else if (!checked && $section.hasClass('has-children')) {
            $section.find('label.checked').each(function () {
                $(this).removeClass('checked');
                $(this).prev('input').removeAttr('checked');
            });
            $filter_ul.slideUp();
        } else if (checked && $filter_ul.is(':hidden')) {
            $filter_ul.slideDown();
        }

        maxcart.run_filters();
    };

    maxcart.orderby = function () {
        maxcart.page_offset = 0;
        maxcart.loading_add( $('.max-product-wrapper') );
        maxcart.ajax_callback();
    };

    maxcart.run_filters = function () {
        maxcart.page_offset = 0;
        maxcart.loading_add( $('.max-product-wrapper') );
        maxcart.ajax_callback();
    };

    $(function maxCartFiltersReady() {
        var $max_orderby = $('.js-max-orderby');
        if ($max_orderby.length) {
            $max_orderby.on('max_selected', function () {
                maxcart.orderby();
            });
        }

        var $max_checkbox_filter = $('.js-max-checkbox');
        if ($max_checkbox_filter.length) {
            $(document).on('max_checked', '.js-max-checkbox', function (e, checked, val) {
                var $self = $(this);
                if ($self.prev('input').data('type') === 'category') {
                    maxcart.category_filter($self, val, checked);
                } else if ($self.prev('input').data('type') === 'price') {
                    if (checked) {
                        $self.closest('.max-filters').slideUp();
                        $('.max-current-filter').text(val).slideDown().on('click', function () {
                            $(this).slideUp();
                            $self.closest('.max-filters').slideDown()
                                .find('.checked')
                                .trigger('click');
                        });
                    }
                    maxcart.run_filters();
                } else {
                    maxcart.run_filters();
                }
            });
        }
    });
})(jQuery);
