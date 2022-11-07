$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/forum/tipi/gestione_forum_tipi_ajax.php',
            {'id': id, 'action': 'get_forum_type_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    console.log(data)
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="descrizione"]').val(datas.descrizione);
                    editForm.find('input[name="pubblico"]').prop("checked", datas.pubblico);
                }
            }
        )
    });
})


function updateForumsTypes(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_forums_types .form').find('select[name="id"]').html(datas.forums_types_list)
    }
}