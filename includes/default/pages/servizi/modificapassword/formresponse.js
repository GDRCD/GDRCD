(function(w, $) {

    w.formresponse = data => {
        $('.response').finish().css('display','none');

        if (data.error === false) {
            $('.response.status_success').fadeIn().delay(5000).fadeOut();
        } else {
            console.warn('Modifica Password Debug: ', data.error);
            $('.response.status_error').fadeIn().delay(5000).fadeOut();
        }
    }

})(window, jQuery);
