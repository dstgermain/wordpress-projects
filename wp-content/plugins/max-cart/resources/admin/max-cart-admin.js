/**
 * General js for Max Cart Admin
 *
 * Created by dstgermain on 3/3/15.
 */

(function maxCartAdmin($) {
    'use strict';

    if (typeof maxcart !== typeof 'object') {
        var maxcart = {};
    }

    maxcart.checkbox_hide = function maxcartHideShow() {
        var $elem = $('[data-maxcart-show]');
        if($elem.length) {
            var $toggle_elem = $elem.data('data-maxcart-show');

            if ($elem.val() !== 'on' && $toggle_elem.is(':hidden')) {
                $toggle_elem.removeClass('hidden');
            } else {
                $toggle_elem.addClass('hidden');
            }
        }
    };

    $(document).ready(function maxCartAdminReady() {
        maxcart.checkbox_hide();
    });

})(jQuery);
