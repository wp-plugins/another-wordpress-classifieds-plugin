if (typeof jQuery !== 'undefined') {

(function($, undefined) {

    $(function() {
        var form = $('#quick-start-guide-notice');
            cancel = form.find('.button');
            submit = form.find('.button-primary'),
            url = form.find('.redirect-url').val(),
            onSuccess = function(redirect) {
                $.ajax(AWPCPAjaxOptions.ajaxurl, {
                    type: 'POST',
                    data: {
                        'action': 'disable-quick-start-guide-notice'
                    },
                    success: function() {
                        if (redirect) {
                            document.location.href = url;
                        } else {
                            form.closest('.update-nag').fadeOut(function() {
                                $(this).remove();
                            });
                        }
                    }
                });
            };

        form.submit(function() { return false; });

        submit.click(function(event) {
            event.preventDefault();
            onSuccess(true);
            return false;
        });

        cancel.click(function(event) {
            event.preventDefault();
            onSuccess(false);
            return false;
        });
    });

})(jQuery);

}