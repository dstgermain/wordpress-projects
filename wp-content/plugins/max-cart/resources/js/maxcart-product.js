if (typeof maxcart !== typeof 'object') {
    var maxcart = {};
}

/**
 * Product associated functions.
 */
(function maxCartProduct($) {
    'use strict';

    maxcart.product = {};

    maxcart.product._add = function addProduct() {

    };

    maxcart.product._remove = function removeProduct() {

    };

    maxcart.product._gallery = function productGallery(self) {
        var $main_image = $('.js-max-main-image'),
            $parent = $main_image.parent();
            //$parent.

        $main_image.attr('src', self.data('large'));
    };

    maxcart.product._enlarge = function productEnlarge(self) {
        var img = self.data('full'),
            block = '<div class="gallery-blackout"><div class="back"></div><div class="image"></div><div class="forward"></div></div>';

        $('body').append(block);

        $(document).on('click', '.gallery-blackout', function() {
            $(this).remove();
        });
    };

    $(function productDocumentReady() {

        var $image_to_enlarge = $('.js-max-view-full');
        if ($image_to_enlarge.length) {
            $image_to_enlarge.on('click', function() {
                maxcart.product._enlarge( $(this) );
            });
        }

        var $image_gallery_item = $('.js-max-gallery-image');
        if ($image_gallery_item.length) {
            $image_gallery_item.on('click', function() {
                maxcart.product._gallery( $(this) );
            });
        }

    });

})(jQuery);
