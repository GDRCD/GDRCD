$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/disponibilita/gestione_disponibilita_ajax.php',
            {'id': id, 'action': 'get_availability_data'},
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


function updateAvailabilities(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_availability_container .form').find('select[name="id"]').html(datas.availabilities_list)
    }
}