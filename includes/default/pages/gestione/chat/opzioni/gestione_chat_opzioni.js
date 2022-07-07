$(function () {

    let editForm = $('.edit-form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val()
        Ajax(
            'gestione/chat/opzioni/gestione_chat_opzioni_ajax.php',
            {'id': id, 'action': 'get_option_data'},
            function (data) {
                if (data != '') {

                    let datas = JSON.parse(data);

                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                    editForm.find('select[name="tipo"]').val(datas.tipo);
                    editForm.find('input[name="titolo"]').val(datas.titolo);
                }
            }
        )
    });
})


function updateOptionsList(data) {

    if (data) {
        let datas = JSON.parse(data);
        $('.gestione_chat_opzioni .form').find('select[name="id"]').html(datas.options_list)
    }
}