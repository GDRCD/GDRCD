$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/gruppi/tipi/gestione_tipi_ajax.php',
            {'id': id, 'action': 'get_type_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                }
            }
        )
    });
})


function updateTypesList(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.group_types_management .form').find('select[name="id"]').html(datas.types_list)
    }
}