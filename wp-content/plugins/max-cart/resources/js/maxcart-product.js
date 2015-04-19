/* global ko, console, localStorage, maxcart */

/**
 * Product associated functions.
 */
(function maxCartProduct($) {
    'use strict';

    function ProcessProducts() {
        var self = this;

        self.item_count = ko.observable(0);
        self.items = ko.observableArray([]);
        self.processing = ko.observable(false);
        self.items_total = ko.observable(0);
        self.zipcode = ko.observable('');
        self.shipping_error = ko.observable(false);
        self.shipping_rate = ko.observable('');
        self.shipping_weight = ko.observable('');
        self.error = ko.observable(false);
        self.error_message = ko.observable('');
        self.cart_total = ko.computed(function () {
            var item_total = self.items_total(),
                shipping_total = self.shipping_rate();

            if (!item_total) {
                item_total = 0;
            }

            if (shipping_total) {
                shipping_total = shipping_total.replace(/\$/g, '');
            } else {
                shipping_total = 0;
            }

            return  parseFloat(shipping_total) + parseFloat(item_total);
        });
        self.hide_cart = ko.computed(function () {
            return self.items().length;
        });

        self.parse_json = function (data) {
            var json = {};

            try {
                json = JSON.parse(data);
            } catch (e) {
                console.log(e + ' : Error parsing json');
            }

            return json;
        };

        self._add = function addProduct() {
            var post_id = $('#product-id').val(),
                name = $('#product-name').val(),
                price = $('#product-price').val(),
                qty = $('#product-qty').val(),
                url = $('#product-url').val(),
                sku = $('#product-sku').val(),
                thumbnail = $('#product-thumbnail').val();

            self.processing(true);

            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'maxcart_add_to_cart',
                    product_id: post_id,
                    product_qty: qty,
                    product_name: name,
                    product_price: price,
                    product_url: url,
                    product_sku: sku,
                    product_thumbnail: thumbnail,
                    _wpnonce: $('#verify_product_add_to_cart').val(),
                    _wp_http_referer: $('[name=_wp_http_referer]').val()
                }
            }).success(function (data) {
                var json = self.parse_json(data);

                if (!json.error_message) {
                    self._create(json);
                    var $quick_cart = $('.maxcart-quickcart'),
                        $quick_cart_table = $('.maxcart-quickcart-table');

                    $quick_cart.addClass('active');
                    $quick_cart_table.slideDown(function () {
                        setTimeout(function () {
                            $quick_cart_table.slideUp(function () {
                                $quick_cart_table.removeAttr('style');
                                $quick_cart.removeClass('active');
                            });
                        }, 5000);
                    });
                } else {
                    self.error(true);
                    self.error_message(json.error_message);

                    setTimeout(function () {
                        self.error(false);
                    }, 10000);
                }
                self.processing(false);
            });
        };
        self._create = function createCart(data) {
            var count = 0;

            if (data && data.items) {
                for (var i = 0; i < data.items.length; i++) {
                    if (data.items[i].qty) {
                        count = count + parseInt(data.items[i].qty, 10);
                    }
                }

                if (maxcart.localStorageSupport()) {
                    self.zipcode(localStorage.getItem('zipcode'));
                }

                var total_weight = 0;
                for (var j = 0; j < data.items.length; j++) {
                    total_weight += parseFloat(data.items[j].weight);
                }

                self.shipping_weight(total_weight);
                self.items(data.items);
                self.item_count(count);
                self.items_total(data.items_total);
            }
        };
        self._remove = function removeProduct(id) {
            self.processing(true);
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'maxcart_remove_from_cart',
                    product_id: this.id
                }
            }).success(function (data) {
                self._create(self.parse_json(data));
                self.processing(false);
            });
        };

        self._getcart = function getCart() {
            self.processing(true);
            $.post('/wp-admin/admin-ajax.php', {action: 'maxcart_quickcart_session'}, function (data) {
                self._create(self.parse_json(data));
                self.processing(false);
            });
        };

        self._updateqty = function updateQty($self) {
            var id = $self.prev().val(),
                qty = $self.val();

            self.processing(true);
            self.shipping_error(false);
            self.shipping_rate('');

            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'maxcart_update_item_qty',
                    product_id: id,
                    product_qty: qty,
                    _wpnonce: $('#verify_maxcart').val()
                }
            }).success(function (data) {
                var json = self.parse_json(data);
                if (json.error_message) {
                    self.error(true);
                    self.error_message(json.error_message);

                    setTimeout(function () {
                        self.error(false);
                    }, 10000);
                }
                self._create(json);
                self.processing(false);
            });
        };
    }

    ko.bindingHandlers.currency = {
        update: function (element, valueAccessor, allBindingsAccessor) {
            return ko.bindingHandlers.text.update(element, function () {
                var value = +(ko.utils.unwrapObservable(valueAccessor()) || 0);
                if (value) {
                    return '$' + value.toFixed(2).replace(/(\d)(?=(\d{3})+\.)/g, '$1,');
                }
            });
        }
    };

    ko.bindingHandlers.fadeVisible = {
        update: function(element, valueAccessor) {
            // Whenever the value subsequently changes, slowly fade the element in or out
            var value = valueAccessor();
            ko.unwrap(value) ? $(element).slideDown() : $(element).slideUp();
        }
    };

    $(function productDocumentReady() {
        var $image_gallery_item = $('.js-max-gallery-image');
        if ($image_gallery_item.length) {
            var $max_product_main_image = $('.max-product-main-image'),
                $max_product_gallery = $('.max-product-gallery');

            $image_gallery_item.on('click', function () {
                var id = $(this).data('gallery-id'),
                    gallery_id = '[data-gallery-id=' + id + ']';

                $max_product_gallery.find('.active').removeClass('active');

                $(this).addClass('active');

                $max_product_main_image.find('.active').animate({opacity: '0'}, function () {
                    $(this).removeClass('active').removeAttr('style');
                    $max_product_main_image.find(gallery_id).addClass('active');
                });
            });
        }

        $(document).on('change', '.js-maxcart-qty-update', function () {
            var update = true;

            if ($(this).val() === '0') {
                update = window.confirm('Are you sure you want to delete this item?');
            }
            if (update) {
                window.ko_maxcart._updateqty($(this));
            }
        });

        var $fancy = $('.fancybox');
        if ($fancy.length) {
            $fancy.fancybox();
        }

        // Activates knockout.js
        window.ko_maxcart = new ProcessProducts();
        window.ko_maxcart._getcart();
        ko.applyBindings(window.ko_maxcart);

    });

})(jQuery);
