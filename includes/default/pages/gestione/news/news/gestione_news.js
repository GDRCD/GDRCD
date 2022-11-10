$(function () {

    let editForm = $('.edit_form');

    editForm.find('select[name="id"]').on('change', function () {
        let id = $(this).val();
        Ajax(
            'gestione/news/news/gestione_news_ajax.php',
            {'id': id, 'action': 'get_news_data'},
            function (data) {
                if (data != '') {
                    let datas = JSON.parse(data);
                    editForm.find('input[name="autore"]').val(datas.autore);
                    editForm.find('input[name="titolo"]').val(datas.titolo);
                    editForm.find('textarea[name="testo"]').val(datas.testo);
                    editForm.find('select[name="tipo"]').val(datas.tipo);
                    editForm.find('input[name="attiva"]').prop("checked", datas.attiva);
                }
            }
        )
    });
})


function updateNews(data) {
    if (data) {
        let datas = JSON.parse(data);
        $('.manage_news_container .form').find('select[name="id"]').html(datas.news_list)
    }
}