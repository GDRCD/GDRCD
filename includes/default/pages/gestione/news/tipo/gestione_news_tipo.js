$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/news/tipo/gestione_news_tipo_ajax.php',
            {'id': id, 'action': 'get_news_type_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="nome"]').val(datas.nome);
                    editForm.find('textarea[name="descrizione"]').val(datas.descrizione);
                    editForm.find('input[name="attiva"]').prop("checked", datas.attiva);
                }
            }
        )
    });
})


function updateNewsTypes(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_news_type_container .form').find('select[name="id"]').html(datas.news_types_list)
    }
}