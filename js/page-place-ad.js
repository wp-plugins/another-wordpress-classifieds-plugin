(function($, undefined) {

    // Show/Hide Payment Terms when a Category is selected
    // Show/Hide Payment Methods when a Payment Term is selected
    $(function() {
        var form = $('#awpcp-place-ad-payment-step-form'),
            terms = form.find('.js-awpcp-payment-term'),
            methods = form.find('.js-awpcp-payment-method');

        var update_payment_methods = function(price) {
            methods.closest('fieldset')[price > 0 ? 'show' : 'hide']();
        };

        var handle_radio_button_click = function(event) {
            var radio = $(this);
            if (radio.attr('checked')) {
                update_payment_methods(radio.closest('tr').data('price'));
            }
        };

        $('#place-ad-category').change(function() {
            var category = $(this).val(),
                categories, enabled;

            if (category <= 0) {
                return terms.show();
            }

            enabled = terms.filter(function() {
                categories = $(this).data('categories');
                return $.inArray(category, categories) > -1 || categories.length === 0;
            });

            terms.hide(); enabled.show();
            form.trigger('awpcp-payment-terms-updated');
        }).change();

        terms.find(':radio').click(handle_radio_button_click).each(handle_radio_button_click);

        form.bind('awpcp-payment-terms-updated', function() {
            var total = 0,
                methods = terms.find('input').filter(':checked').closest('tr');

            if (methods.length === 0) {
                methods = terms.filter(':visible');
            }

            methods.map(function() {
                total += $(this).data('price');
            });

            update_payment_methods(total);
        });
    });


    // Update Ad Details fields related to user information everytimes an
    // user is selected in the users dropdown (available to administrators)
    $(function() {
        var name, email, users;

        name = $('input[name=adcontact_name]');
        email = $('input[name=adcontact_email]');
        state = $('input[name=adcontact_state]');
        city = $('input[name=adcontact_city]');
        website = $('input[name=websiteurl]');

        users = $('#place-ad-user-id').change(function() {
            var id = users.val(), done = false;
            $.each(AWPCP_Users, function(k, user) {
                if (user.ID == id) {
                    name.val(user.first_name + ' ' + user.last_name);
                    email.val(user.user_email);
                    city.val(user.city);
                    state.val(user.state);
                    website.val(user.user_url);
                    done = true;
                }
                return !done;
            });

            if (!done) {
                name.val('');
                email.val('');
            }
        });
    });
})(jQuery);