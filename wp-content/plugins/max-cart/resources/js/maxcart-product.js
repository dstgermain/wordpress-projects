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
        self.cart_total = ko.computed(function () {
            var item_total = self.items_total(),
                shipping_total = self.shipping_rate();

            if (item_total) {
                item_total = item_total.replace(/\$/g, '');
            } else {
                item_total = 0;
            }
            if (shipping_total) {
                shipping_total = shipping_total.replace(/\$/g, '');
            } else {
                shipping_total = 0;
            }
            console.log(item_total);
            return  parseFloat(shipping_total) + parseFloat(item_total);
        });
        self.hide_cart = ko.computed(function () {
            return self.items().length;
        });

        self._add = function addProduct() {
            var post_id = $('#product-id').val(),
                name = $('#product-name').val(),
                price = $('#product-price').val(),
                qty = $('#product-qty').val(),
                url = $('#product-url').val(),
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
                    product_thumbnail: thumbnail,
                    _wpnonce: $('#verify_product_add_to_cart').val(),
                    _wp_http_referer: $('[name=_wp_http_referer]').val()
                }
            }).success(function (data) {
                self._create(data);
                var $quick_cart = $('.maxcart-quickcart'),
                    $quick_cart_table = $('.maxcart-quickcart-table');

                $quick_cart.addClass('active');
                $quick_cart_table.slideDown(function () {
                    setTimeout(function () {
                        $quick_cart_table.slideUp(function () {
                            $quick_cart_table.removeAttr('style');
                            $quick_cart.removeClass('active');
                        });
                    }, 4000);
                });
                self.processing(false);
            });
        };
        self._create = function createCart(data) {
            var json = null,
                count = 0;

            try {
                json = JSON.parse(data);
            } catch (e) {
                console.log(e + ' : Error parsing json');
            }

            if (json && json.items) {
                for (var i = 0; i < json.items.length; i++) {
                    if (json.items[i].qty) {
                        count = count + parseInt(json.items[i].qty, 10);
                    }
                }

                if (maxcart.localStorageSupport()) {
                    self.zipcode(localStorage.getItem('zipcode'));
                }

                self.items(json.items);
                self.item_count(count);
                self._gettotal();
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
                self._create(data);
                self.processing(false);
            });
        };
        self._gettotal = function getTotal() {
            var self = this,
                total = 0;
            if (self.item_count) {
                for (var i = 0; i < self.items().length; i++) {
                    for (var j = 0; j < self.items()[i].qty; j++) {
                        total = total + parseFloat(self.items()[i].price);
                    }
                }

                self.items_total('$' + total.toFixed(2));
            }
        };

        self._getcart = function addProduct() {
            self.processing(true);
            $.post('/wp-admin/admin-ajax.php', {action: 'maxcart_quickcart_session'}, function (data) {
                self._create(data);
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
                self._create(data);
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
