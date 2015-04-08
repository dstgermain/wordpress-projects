/**
 * Created by dstgermain on 4/4/15.
 */

/* global console */

(function maxcartCart($) {
    $(function maxcartCartReady() {
        $(document).on('click', '.js-shipping-estimate', function () {
            var val = $(this).prev().val();
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
                    window.ko_maxcart.shipping_rate(data.replace(/\"/g, ''));
                    window.ko_maxcart.processing(false);
                });
            }
        });
    });
})(jQuery);
