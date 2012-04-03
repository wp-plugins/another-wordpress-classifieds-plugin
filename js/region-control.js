if (typeof jQuery != 'undefined') {
    (function($, undefined) {
        $.RegionsField = function(element, field) {
            var self = this;

            self.field = field;
            self.element = $(element).data('RegionsField', this);

            self.name = self.element.data('region-field-name');

            self.input = self.element.find('input');
            self.select = self.element.find('select');
            self.helptext = self.element.find('.helptext');

            self.element.bind('awpcp-update-region-options', function(event, type, value) {
                if (type === 'Country' && $.inArray(field, ['State', 'City', 'County']) > -1) {
                    self.update(type, value);
                } else if (type === 'State' && $.inArray(field, ['City', 'County']) > -1) {
                    self.update(type, value);
                } else if (type === 'City' && $.inArray(field, ['County']) > -1) {
                    self.update(type, value);
                } else if (type === 'County') {
                    // nothing!
                }
            });
        };

        $.RegionsField.prototype = {
            url: AWPCP.ajaxurl || '/wp-admin/admin-ajax.php',
            update: function(filterby, value) {
                var self = this,
                    options = self.select.find('option').remove();

                if (value.length <= 0) {
                    self.select.addClass('hidden').removeAttr('name');
                    self.input.removeClass('hidden').attr('name', self.name);
                    self.input.val('');
                    self.helptext.removeClass('hidden');
                    return;
                } else {
                    self.select.append('<option value="">Updating...</option>');
                }

                // Get list of Regions and create a dropdown. If no regions
                // are returned replace the select dropdown with the textfield
                $.getJSON(self.url, {
                            action: 'awpcp-search-ads-get-regions',
                            field: self.field,
                            filterby: filterby,
                            value: value
                          },
                          function(response, status, xhr) {
                    if (response.status === 'ok') {
                        if (response.entries.length > 0) {
                            self.input.addClass('hidden').removeAttr('name');
                            self.helptext.addClass('hidden');
                            self.select.removeClass('hidden').attr('name', self.name);
                            self.select.empty().append($(response.html)).val('');
                        } else {
                            self.select.addClass('hidden').removeAttr('name');
                            self.input.removeClass('hidden').attr('name', self.name);
                            self.input.val('');
                            self.helptext.removeClass('hidden');
                        }
                    } else {
                        // TODO: tell the user an error ocurred
                        element.empty().append('<option value="">No Regions available</option>');
                    }
                });
            }
        };

        $.fn.RegionsField = function(field) {
            return this.each(function() {
                var element = $(this);
                if (!element.data('RegionsField')) {
                    new $.RegionsField(element, field);
                }
            });
        };

        /* Search Ads and Place Ad page */
        $('.awpcp-region-control-region-fields').each(function() {
            var fields = $(this),
                country = fields.find('[region-field="country"]').RegionsField('Country'),
                state = fields.find('[region-field="state"]').RegionsField('State'),
                city = fields.find('[region-field="city"]').RegionsField('City'),
                village = fields.find('[region-field="county"]').RegionsField('County');

            country.delegate('input,select', 'change', function(event) {
                var value = $(event.target).val(),
                    params = ['Country', value];
                state.trigger('awpcp-update-region-options', params);
                city.trigger('awpcp-update-region-options', params);
                village.trigger('awpcp-update-region-options', params);
            });

            state.delegate('input,select', 'change', function(event) {
                var value = $(event.target).val(),
                    params = ['State', value];
                city.trigger('awpcp-update-region-options', params);
                village.trigger('awpcp-update-region-options', params);
            });

            city.delegate('input,select', 'change', function(event) {
                var value = $(event.target).val(),
                    params = ['City', value];
                village.trigger('awpcp-update-region-options', params);
            });
        });
    })(jQuery);
}