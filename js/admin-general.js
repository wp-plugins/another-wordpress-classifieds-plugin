if (typeof jQuery !== 'undefined') {

(function($, undefined) {

    $(function() {
        var guide = $('#quick-start-guide-notice');
            cancel = guide.find('.button');
            submit = guide.find('.button-primary'),
            url = guide.attr('data-url'),

            onSuccess = function(redirect) {
                $.ajax(AWPCPAjaxOptions.ajaxurl, {
                    type: 'POST',
                    data: {
                        'action': 'disable-quick-start-guide-notice'
                    },
                    success: function() {
                        guide.closest('.update-nag').fadeOut(function() {
                            $(this).remove();
                        });
                    }
                });
            };

        submit.click(function(event) {
            onSuccess(true);
        });

        cancel.click(function(event) {
            onSuccess(false);
        });
    });

})(jQuery);

}