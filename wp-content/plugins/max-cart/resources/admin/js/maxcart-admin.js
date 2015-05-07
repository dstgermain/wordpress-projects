/**
 * General js for Max Cart Admin
 *
 * Created by dstgermain on 3/3/15.
 */

if (typeof maxcart !== typeof 'object') {
    var maxcart = {};
}

(function maxCartAdmin($) {
    'use strict';

    maxcart.checkbox_hide = function maxcartHideShow() {
        var $elem = $('.js-maxcart-show');
        if($elem.length) {
            var toggle_elem = $elem.data('show'),
                $toggle_elem = $(toggle_elem);

            $elem.on('click', function checkBoxToggle(){
                if (!$elem.is(':checked') && $toggle_elem.is(':hidden')) {
                    $toggle_elem.removeClass('hidden');
                } else {
                    $toggle_elem.addClass('hidden');
                }
            });
        }
    };

    maxcart.manage_gallery = function maxcartManageGallery() {
        var $ids = $('.js-maxcart-product-ids');
        $('.js-maxcart-manage-gallery').click(function() {
            var gallery_sc = '[gallery ids="' + ($ids.val() ? $ids.val() : '0') + '"]';

            wp.media.gallery.edit(gallery_sc).on('update', function(g) {
                console.log(gallery_sc);
                var id_array = [];
                $.each(g.models, function(id, img) { id_array.push(img.id); });
                $ids.val(id_array.join(','));

                if($ids.val()) {
                    $('.js-maxcart-manage-gallery').val('Edit Gallery');
                }
            });
        });
    };

    $(document).ready(function maxCartAdminReady() {
        maxcart.checkbox_hide();
        maxcart.manage_gallery();
    });
})(jQuery);
