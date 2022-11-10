$(function () {

    $('.abilita_scheda_nome span').on('click', function (e) {
        e.stopImmediatePropagation();

        $(this).closest('.tr').find('.abilita_scheda_text').slideToggle();
    })
})
