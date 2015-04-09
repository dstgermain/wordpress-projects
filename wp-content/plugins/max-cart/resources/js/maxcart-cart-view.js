/**
 * Created by dstgermain on 4/4/15.
 */

/* global console, maxcart */

(function maxcartCart($) {
    $(function maxcartCartReady() {
        $(document).on('click', '.js-shipping-estimate', function () {
            var val = $(this).prev().val();
            if (maxcart.localStorageSupport()) {
                localStorage.setItem('zipcode', val);
                window.ko_maxcart.zipcode(localStorage.getItem('zipcode'));
            }
            if (val) {
                window.ko_maxcart.processing(true);
                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'maxcart_get_shipping',
                        zipcode: $(this).prev().val(),
                        _wpnonce: $('#verify_maxcart').val()
                    }
                }).success(function (data) {
                    if (data !== 'false') {
                        window.ko_maxcart.shipping_error(false);
                        window.ko_maxcart.shipping_rate(data.replace(/\"/g, ''));
                    } else {
                        window.ko_maxcart.shipping_error(true);
                    }
                    window.ko_maxcart.processing(false);
                });
            }
        });
    });
})(jQuery);
