$(function () {

    // class open_text show hidden_text on same row
    $('body').on('click', '.fake-table .open_text', function (e) {
        e.stopImmediatePropagation();

        $(this).closest('.tr').find('.hidden_text').slideToggle('fast');

    })


});