$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/forum/forum/gestione_forum_ajax.php',
            {'id': id, 'action': 'get_forum_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('input[name="descrizione"]').val(datas.descrizione);
                    editForm.find('select[name="tipo"]').val(datas.tipo);
                }
            }
        )
    });
})


function updateForums(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_forums .form').find('select[name="id"]').html(datas.forums_list)
    }
}