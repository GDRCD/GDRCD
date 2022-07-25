$(function(){

    $('.abilita_scheda_nome').on('click', function (e) {
        e.stopImmediatePropagation();

        $(this).closest('.tr').find('.abilita_scheda_text').slideToggle();
    })
})

function updateAbilities(data) {

    if (data) {
        let datas = JSON.parse(data);

        $('.scheda_abilita_box .ability_table').html(datas.new_template)
        $('.form_info .remained_exp').html(datas.remained_exp)
    }
}
