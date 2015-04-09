/* global google, console, maxcart */
(function maxcartCheckout($) {
    'use strict';

    var autocomplete,
        componentForm = {
            address1: 'route',
            city: 'locality',
            state: 'administrative_area_level_1',
            country: 'country',
            zip: 'postal_code'
        },
        valid = true;

    var maxcart_checkout = (function maxCartCheckoutInit() {
        var init = function initialize() {
            autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById('address1')),
                { country: 'US', types: ['geocode'] });
            google.maps.event.addListener(autocomplete, 'place_changed', function () {
                fill_in_address();
            });
        };

        var validate_email = function (email) {
            var re = /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
            return re.test(email);
        };

        var validate_number = function (number) {
            var re = /^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/;
            return re.test(number);
        };

        var show_error = function showError() {
            $('html, body').animate({scrollTop: ($('.has-error').first().offset().top - 50)}, 500, 'swing', function () {
                $('.has-error').find('input').first().focus();
            });
        };

        var validation = function validation() {
            var form_obj = {},
                $form;

            window.ko_maxcart.processing(true);

            valid = true;

            $('.js-form-validation').find('input, textarea').each(function () {
                $form = $(this);

                var val = $form.val(),
                    id = $form.attr('id'),
                    email = true,
                    number = true;

                if (id === 'email') {
                    email = validate_email(val);
                } else if (id === 'phone') {
                    number = validate_number(val);
                }

                if (val && email && number) {
                    form_obj[id] = val;
                } else if ($form.attr('required')) {
                    $form.closest('.form-group').addClass('has-error');
                    valid = false;
                }
            });

            if (valid) {
                $('.js-form-validation').submit();
            } else {
                window.ko_maxcart.processing(false);
                show_error();
            }
        };

        var fill_in_address = function fillInAddress() {
            var place = autocomplete.getPlace();

            for (var component in componentForm) {
                document.getElementById(component).value = '';
            }

            var street_number = 0,
                street = '',
                city = '',
                state = '',
                country = '',
                zip = '';

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                switch (addressType) {
                    case 'street_number':
                        street_number = place.address_components[i].long_name;
                        break;
                    case 'route':
                        street = place.address_components[i].long_name;
                        break;
                    case 'locality':
                        city = place.address_components[i].long_name;
                        break;
                    case 'administrative_area_level_1':
                        state = place.address_components[i].short_name;
                        break;
                    case 'country':
                        country = place.address_components[i].long_name;
                        break;
                    case 'postal_code':
                        zip = place.address_components[i].long_name;
                        break;
                }

                $('#address1').val(street_number + ' ' + street);
                $('#city').val(city);
                $('#state').val(state);
                $('#country').val(country);
                $('#zip').val(zip).trigger('updated');
                window.ko_maxcart.zipcode(zip);
            }
        };

        // Bias the autocomplete object to the user's geographical location,
        // as supplied by the browser's 'navigator.geolocation' object.
        var geolocate = function geolocate() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var geolocation = new google.maps.LatLng(
                        position.coords.latitude, position.coords.longitude);
                    var circle = new google.maps.Circle({
                        center: geolocation,
                        radius: position.coords.accuracy
                    });
                    autocomplete.setBounds(circle.getBounds());
                });
            }
        };

        var get_shipping = function getShipping($self){
            var val = $self.val();
            if (maxcart.localStorageSupport()) {
                localStorage.setItem('zipcode', val);
                window.ko_maxcart.zipcode(localStorage.getItem('zipcode'));
            }
            console.log(val);
            if (val) {
                window.ko_maxcart.processing(true);
                $.ajax({
                    url: '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'maxcart_get_shipping',
                        zipcode: val,
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
        };

        return {
            init: init,
            geolocate: geolocate,
            get_shipping: get_shipping,
            validate: validation,
            show_error: show_error
        };
    })();

    $(function maxcartCheckoutReady() {
        maxcart_checkout.init();

        $('#address1').on('focus', function () {
            maxcart_checkout.geolocate();
        });

        $(document).on('blur', '#zip', function () {
            if ($(this).val()) {
                maxcart_checkout.get_shipping($(this));
            }
        });

        $(document).on('updated', '#zip', function () {
            if ($(this).val()) {
                maxcart_checkout.get_shipping($(this));
            }
        });

        $(document).on('click', '.js-calculate-shipping', function () {
            var $zip = $('#zip');
            if ($zip.val()) {
                maxcart_checkout.get_shipping($zip);
            } else {
                $zip.closest('.form-group').addClass('has-error');
                maxcart_checkout.show_error();
            }
        });

        $(document).on('click', '.js-submit-checkout-form', function () {
            $('.form-group').removeClass('has-error');
            maxcart_checkout.validate();
        });

        $(document).on('blur', 'input', function () {
            $(this).closest('.form-group').removeClass('has-error');
        });

    });

})(jQuery);
