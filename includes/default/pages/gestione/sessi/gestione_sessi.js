$(function(){

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/sessi/gestione_sessi_ajax.php',
            {'id': id, 'action': 'get_gender_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="immagine"]').val(datas.immagine);
                }
            }
        )
    });
})


function updateGenders(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_gender_container .form').find('select[name="id"]').html(datas.genders_list)
    }
}