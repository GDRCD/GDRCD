$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/abilita/abilita/gestione_abilita_ajax.php',
            {'id': id, 'action': 'get_ability_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                    editForm.find('select[name="statistica"]').val(datas.statistica);
                    editForm.find('select[name="razza"]').val(datas.razza);
                }
            }
        )
    });
})


function updateAbilities(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_ability_container .form').find('select[name="id"]').html(datas.ability_list)
    }
}