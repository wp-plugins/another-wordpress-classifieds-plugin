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
                update_payment_methods(radio.closest('tr').attr('data-price'));
            }
        };

        $('#place-ad-category').change(function() {
            var category = $(this).val(),
                categories, enabled;

            if (category <= 0) {
                return terms.show();
            }

            enabled = terms.filter(function() {
                categories = $.parseJSON($(this).attr('data-categories'));
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
                total += $(this).attr('data-price');
            });

            update_payment_methods(total);
        });
    });


    // Update Ad Details fields related to user information everytime an
    // user is selected in the users dropdown (available to administrators)
    $(function() {
        var form = $('#adpostform'),
            name, email, state, city, website,
            users, categories, cats,
            terms, terms_parent,
            items, item,
            user, user_payment_terms;

        name = form.find('input[name=adcontact_name]');
        email = form.find('input[name=adcontact_email]');
        state = form.find('input[name=adcontact_state], select[name=adcontact_state]');
        city = form.find('input[name=adcontact_city], select[name=adcontact_city]');
        website = form.find('input[name=websiteurl]');

        categories = form.find('[name=adcategory]');

        terms = $('#place-ad-user-payment-terms');
        terms_parent = terms.closest('p').hide();

        var update_payment_terms = function(id, category) {
            id = parseInt(id, 10);

            if (isNaN(id) || id === 0) {
                selector = '[value]';
            } else {
                selector = '#payment-term-default, #payment-term-';
                user_payment_terms = users.find('[value=' + id + ']').attr('data-payment-terms') || [];
                selector+= user_payment_terms.split(',').join(', #payment-term-');
            }

            terms_parent.show();

            item = null;
            items = terms.find('option').hide().filter(selector);

            if (category.length > 0) {
                items = items.filter(function() {
                    cats = $.parseJSON($(this).attr('data-categories'));
                    return $.inArray(category, cats) > -1 || cats.length === 0;
                });
            }

            // two items: the default and one actual payment term
            if (items.length == 2) {
                item = items.filter(':not(#payment-term-default)').show();
            } else {
                items.show();
            }

            if (!terms.find(':selected').is(':visible') && item === null) {
                terms.val('');
            } else if (item !== null) {
                terms.val(item.attr('value'));
            }
        };
        
        categories.change(function() {
            if (users.length > 0) {
                update_payment_terms(users.val(), $(this).val());
            }
        });

        users = $('#place-ad-user-id').change(function() {
            var id = users.val(),
                payment_terms = null,
                selector = null,
                done = false;

            if (id === 0) {
                terms_parent.hide();
            } else {
                $.each(AWPCP_Users, function(k, user) {
                    if (user.ID == id) {
                        name.val(user.first_name + ' ' + user.last_name);
                        email.val(user.user_email);
                        website.val(user.user_url);

                        var field = state.filter(':visible');
                        if (field.length > 0 && field[0].tagName.toLowerCase() == 'select') {
                            city.one('awpcp-update-region-options-completed', function() {
                                city.val(user.city).change();
                            });
                            state.val(user.state).change();
                        } else {
                            state.val(user.state).change();
                            city.val(user.city).change();
                        }

                        update_payment_terms(id, categories.val());

                        // show message about empty fields
                        var _fields = [{name: 'First Name', value: user.first_name},
                                   {name: 'Last Name', value: user.last_name},
                                   {name: 'Email', value: user.user_email},
                                   {name: 'Website', value: user.user_url},
                                   {name: 'State', value: user.state},
                                   {name: 'City', value: user.city}],
                            empty_fields = [],
                            message = $('<span class="error message"></span>');

                        $.each(_fields, function(k, _field) {
                            if (_field.value.length === 0) {
                                empty_fields.push(_field.name);
                            }
                        });

                        users.nextAll('br, span.message').remove();
                        if (empty_fields.length > 0) {
                            message.text('This user has empty profile fields for ' + empty_fields.join(', ') + '.');
                            users.closest('.awpcp-form-spacer').append('<br/>').append(message);
                        }

                        done = true;
                    }
                    return !done;
                });
            }

            if (id > 0 && !done) {
                name.val('');
                email.val('');
                website.val('');
                state.val('');
                city.val('');
            }
        });

        if (users.length > 0) {
            users.change();
        }
    });
})(jQuery);